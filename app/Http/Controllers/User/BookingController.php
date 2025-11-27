<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\Kamar;
use App\Models\Pemesanan;
use App\Models\Fasilitas;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showBookingForm(Kamar $kamar)
    {
        // 1. TAMBAHAN PENTING: Cek apakah user punya pesanan yang masih 'pending'
        // Jika ada, paksa user untuk menyelesaikannya terlebih dahulu
        $pendingBooking = Pemesanan::where('user_id', Auth::id())
            ->where('status_pemesanan', 'pending')
            ->first();

        if ($pendingBooking) {
            return redirect()->route('booking.payment', $pendingBooking->id_pemesanan)
                ->with('error', 'Anda masih memiliki pesanan yang belum diselesaikan. Silakan bayar atau batalkan pesanan tersebut untuk memesan kamar baru.');
        }

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
        // 2. TAMBAHAN PENTING: Cek lagi di method store untuk keamanan ganda
        $pendingBooking = Pemesanan::where('user_id', Auth::id())
            ->where('status_pemesanan', 'pending')
            ->first();

        if ($pendingBooking) {
            return redirect()->route('booking.payment', $pendingBooking->id_pemesanan)
                ->with('error', 'Transaksi sebelumnya belum selesai. Harap selesaikan pembayaran terlebih dahulu.');
        }

        $request->validate([
            'kamar_id' => 'required|exists:kamars,id_kamar',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'jumlah_tamu' => 'required|integer|min:1',
            'fasilitas_ids' => 'nullable|array',
            'fasilitas_ids.*' => 'exists:fasilitas,id_fasilitas',
        ]);

        // Gunakan lockForUpdate untuk mencegah race condition (opsional tapi disarankan)
        try {
            // Mulai Transaksi Database
            return DB::transaction(function () use ($request) {

                // Lock kamar untuk mencegah race condition
                $kamar = Kamar::where('id_kamar', $request->kamar_id)->lockForUpdate()->first();

                // Cek ketersediaan di dalam lock
                if ($kamar->status_kamar == 0) {
                    // Throw exception agar di-catch catch block atau return redirect
                    throw new \Exception('Maaf, kamar ini baru saja dibooking.');
                }

                // ... (Logika hitung harga tetap sama) ...
                $kamar->load('tipeKamar');
                $checkIn = Carbon::parse($request->check_in_date);
                $checkOut = Carbon::parse($request->check_out_date);
                $durasi = $checkIn->diffInDays($checkOut) ?: 1;

                $totalHarga = $kamar->tipeKamar->harga_per_malam * $durasi;

                // Hitung fasilitas
                if ($request->has('fasilitas_ids')) {
                    $fasilitas = Fasilitas::whereIn('id_fasilitas', $request->fasilitas_ids)->get();
                    $totalHarga += $fasilitas->sum('biaya_tambahan');
                }

                // Create Pemesanan
                $pemesanan = Pemesanan::create([
                    'user_id' => Auth::id(),
                    'kamar_id' => $kamar->id_kamar,
                    'check_in_date' => $request->check_in_date,
                    'check_out_date' => $request->check_out_date,
                    'jumlah_tamu' => $request->jumlah_tamu,
                    'total_harga' => $totalHarga,
                    'status_pemesanan' => 'pending',
                ]);

                // Attach Fasilitas
                if ($request->has('fasilitas_ids')) {
                    $pemesanan->fasilitas()->attach($request->fasilitas_ids);
                }

                // Update Status Kamar
                $kamar->save();

                return redirect()->route('booking.payment', $pemesanan->id_pemesanan)
                    ->with('success', 'Pesanan berhasil! Silakan selesaikan pembayaran.');
            });

        } catch (\Exception $e) {
            // Jika ada error, semua perubahan di atas dibatalkan (rollback)
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
    }

    // ... (method showPayment, checkPaymentStatus, dll biarkan seperti semula)
    public function showPayment($id)
    {
        // Kode existing Anda ...
        $pemesanan = Pemesanan::with(['kamar.tipeKamar'])->findOrFail($id);

        // ... dst
        if ($pemesanan->user_id !== Auth::id()) {
            abort(403);
        }

        if ($pemesanan->status_pemesanan !== 'pending') {
            return redirect()->route('dashboard');
        }

        $waktuDibuat = Carbon::parse($pemesanan->created_at);
        $batasWaktu = $waktuDibuat->addMinutes(10);
        $sisaWaktuDetik = Carbon::now()->diffInSeconds($batasWaktu, false);

        if ($sisaWaktuDetik <= 0) {
            $pemesanan->update(['status_pemesanan' => 'cancelled']);
            $kamar = $pemesanan->kamar;
            $kamar->status_kamar = 1;
            $kamar->save();
            return redirect()->route('dashboard')->with('error', 'Waktu pembayaran telah habis.');
        }

        return view('user.payment', compact('pemesanan', 'batasWaktu'));
    }

    // ... Method checkPaymentStatus, cancelBooking, simulatePaymentSuccess tetap sama ...
    public function checkPaymentStatus($id)
    {
        $pemesanan = Pemesanan::findOrFail($id);

        if ($pemesanan->status_pemesanan == 'confirmed') {
            return response()->json(['status' => 'success']);
        }

        $batasWaktu = Carbon::parse($pemesanan->created_at)->addMinutes(10);

        if (Carbon::now()->greaterThan($batasWaktu)) {
            $pemesanan->update(['status_pemesanan' => 'cancelled']);
            $kamar = $pemesanan->kamar;
            $kamar->status_kamar = 1;
            $kamar->save();
            return response()->json(['status' => 'expired']);
        }

        if ($pemesanan->status_pemesanan == 'cancelled') {
            return response()->json(['status' => 'expired']);
        }

        return response()->json(['status' => 'pending']);
    }

    public function cancelBooking($id)
    {
        $pemesanan = Pemesanan::findOrFail($id);

        if ($pemesanan->user_id == Auth::id() && $pemesanan->status_pemesanan == 'pending') {
            $pemesanan->update(['status_pemesanan' => 'cancelled']);
            $kamar = $pemesanan->kamar;
            $kamar->status_kamar = 1;
            $kamar->save();
            return redirect()->route('dashboard')->with('success', 'Pesanan dibatalkan.');
        }

        return back();
    }

    public function detail($id)
    {
        // Ambil data pesanan beserta relasi kamar, tipe kamar, dan fasilitas
        $pemesanan = Pemesanan::with(['kamar.tipeKamar', 'user', 'fasilitas'])
            ->findOrFail($id);

        // Keamanan: Pastikan user yang login adalah pemilik pesanan ini
        if (auth()->id() !== $pemesanan->user_id) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI.');
        }

        return view('user.pages.order-detail', compact('pemesanan'));
    }

    public function simulatePaymentSuccess($id)
    {
        $pemesanan = Pemesanan::with('kamar')->findOrFail($id);

        if ($pemesanan->status_pemesanan == 'pending') {
            $pemesanan->update(['status_pemesanan' => 'confirmed']);
            return redirect()->route('dashboard')->with('success', 'Pembayaran Berhasil! Kamar Berhasil Dipesan.');
        }

        return "Pesanan tidak valid.";
    }
}
