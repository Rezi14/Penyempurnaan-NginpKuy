<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\Kamar;
use App\Models\Pemesanan;
use App\Models\Fasilitas;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showBookingForm(Kamar $kamar)
    {
        // Cek jika kamar sedang tidak tersedia (preventif jika ada yang akses URL langsung)
        if ($kamar->status_kamar == 0) {
             return redirect()->route('dashboard')->with('error', 'Maaf, kamar ini baru saja dipesan orang lain.');
        }

        $kamar->load('tipeKamar');
        // Hanya ambil fasilitas dengan biaya tambahan > 0
        $fasilitasTersedia = Fasilitas::where('biaya_tambahan', '>', 0)->get();

        return view('user.booking', compact('kamar', 'fasilitasTersedia'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kamar_id' => 'required|exists:kamars,id_kamar',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'jumlah_tamu' => 'required|integer|min:1',
            'fasilitas_ids' => 'nullable|array',
            'fasilitas_ids.*' => 'exists:fasilitas,id_fasilitas',
        ]);

        // Gunakan lockForUpdate untuk mencegah race condition (opsional tapi disarankan)
        $kamar = Kamar::where('id_kamar', $request->kamar_id)->lockForUpdate()->first();

        // Cek lagi ketersediaan kamar sebelum menyimpan
        if ($kamar->status_kamar == 0) {
            return redirect()->route('dashboard')->with('error', 'Maaf, kamar ini baru saja dibooking oleh pengguna lain.');
        }

        $kamar->load('tipeKamar');

        $checkIn = Carbon::parse($request->check_in_date);
        $checkOut = Carbon::parse($request->check_out_date);
        $durasiMenginap = $checkIn->diffInDays($checkOut);
        if ($durasiMenginap == 0) {
            $durasiMenginap = 1;
        }

        $hargaPerMalam = $kamar->tipeKamar->harga_per_malam;
        $totalHarga = $hargaPerMalam * $durasiMenginap;

        // Hitung biaya tambahan fasilitas
        if ($request->has('fasilitas_ids') && is_array($request->fasilitas_ids)) {
            $fasilitasDipilih = Fasilitas::whereIn('id_fasilitas', $request->fasilitas_ids)->get();
            foreach ($fasilitasDipilih as $fasilitas) {
                $totalHarga += $fasilitas->biaya_tambahan;
            }
        }

        // 1. Buat Pemesanan
        $pemesanan = Pemesanan::create([
            'user_id' => Auth::id(),
            'kamar_id' => $kamar->id_kamar,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'jumlah_tamu' => $request->jumlah_tamu,
            'total_harga' => $totalHarga,
            'status_pemesanan' => 'pending',
        ]);

        if ($request->has('fasilitas_ids') && is_array($request->fasilitas_ids)) {
            $pemesanan->fasilitas()->attach($request->fasilitas_ids);
        }

        // 2. UPDATE PENTING: Set Kamar jadi TIDAK TERSEDIA (0) saat ini juga
        $kamar->status_kamar = 0;
        $kamar->save();

        return redirect()->route('booking.payment', $pemesanan->id_pemesanan)
                         ->with('success', 'Pesanan berhasil! Silakan selesaikan pembayaran dalam 10 menit.');
    }

    public function showPayment($id)
    {
        $pemesanan = Pemesanan::with(['kamar.tipeKamar'])->findOrFail($id);

        if ($pemesanan->user_id !== Auth::id()) {
            abort(403);
        }

        if ($pemesanan->status_pemesanan !== 'pending') {
            return redirect()->route('dashboard');
        }

        $waktuDibuat = Carbon::parse($pemesanan->created_at);
        $batasWaktu = $waktuDibuat->addMinutes(10);
        $sisaWaktuDetik = Carbon::now()->diffInSeconds($batasWaktu, false);

        // Jika waktu habis saat user refresh halaman payment
        if ($sisaWaktuDetik <= 0) {
            // Cancel pesanan
            $pemesanan->update(['status_pemesanan' => 'cancelled']);

            // Kembalikan status kamar jadi TERSEDIA (1)
            $kamar = $pemesanan->kamar;
            $kamar->status_kamar = 1;
            $kamar->save();

            return redirect()->route('dashboard')->with('error', 'Waktu pembayaran telah habis.');
        }

        return view('user.payment', compact('pemesanan', 'batasWaktu'));
    }

    // Dipanggil via AJAX setiap detik
    public function checkPaymentStatus($id)
    {
        $pemesanan = Pemesanan::findOrFail($id);

        // A. Jika Sukses Dibayar
        if ($pemesanan->status_pemesanan == 'confirmed') {
            return response()->json(['status' => 'success']);
        }

        // B. Cek Waktu Habis (Auto Cancel)
        $batasWaktu = Carbon::parse($pemesanan->created_at)->addMinutes(10);

        if (Carbon::now()->greaterThan($batasWaktu)) {
            // 1. Batalkan Pesanan
            $pemesanan->update(['status_pemesanan' => 'cancelled']);

            // 2. PENTING: Kembalikan Kamar jadi TERSEDIA (1)
            $kamar = $pemesanan->kamar;
            $kamar->status_kamar = 1;
            $kamar->save();

            return response()->json(['status' => 'expired']);
        }

        // C. Jika dibatalkan manual oleh sistem lain/admin
        if ($pemesanan->status_pemesanan == 'cancelled') {
             return response()->json(['status' => 'expired']);
        }

        return response()->json(['status' => 'pending']);
    }

    // User klik tombol "Batalkan Pesanan"
    public function cancelBooking($id)
    {
        $pemesanan = Pemesanan::findOrFail($id);

        if ($pemesanan->user_id == Auth::id() && $pemesanan->status_pemesanan == 'pending') {
            // 1. Update status pesanan
            $pemesanan->update(['status_pemesanan' => 'cancelled']);

            // 2. PENTING: Kembalikan Kamar jadi TERSEDIA (1)
            $kamar = $pemesanan->kamar;
            $kamar->status_kamar = 1;
            $kamar->save();

            return redirect()->route('dashboard')->with('success', 'Pesanan dibatalkan.');
        }

        return back();
    }

    // --- SIMULASI PEMBAYARAN SUKSES ---
    public function simulatePaymentSuccess($id)
    {
        $pemesanan = Pemesanan::with('kamar')->findOrFail($id);

        if ($pemesanan->status_pemesanan == 'pending') {
            // Ubah status pemesanan jadi Lunas/Confirmed
            $pemesanan->update(['status_pemesanan' => 'confirmed']);

            // NOTE: Kita TIDAK PERLU mengubah status kamar di sini.
            // Karena status kamar sudah di-set jadi 0 (Tidak Tersedia) saat di method store().
            // Jadi kamar tetap terkunci untuk user ini.

            return redirect()->route('dashboard')->with('success', 'Pembayaran Berhasil! Kamar Berhasil Dipesan.');
        }

        return "Pesanan tidak valid.";
    }
}
