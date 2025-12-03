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

    /**
     * Helper untuk menentukan batas tamu berdasarkan nama tipe kamar
     */
    private function getMaxGuestLimit($namaTipeKamar)
    {
        // Normalisasi string (huruf kecil semua) agar pencocokan lebih aman
        $type = strtolower($namaTipeKamar);

        if (str_contains($type, 'family')) {
            return 8;
        } elseif (str_contains($type, 'suite')) {
            return 6;
        } elseif (str_contains($type, 'deluxe')) {
            return 4;
        } else {
            // Default untuk Standard dan lain-lain
            return 2;
        }
    }

    public function showBookingForm(Kamar $kamar)
    {
        // 1. Cek pending booking
        $pendingBooking = Pemesanan::where('user_id', Auth::id())
            ->where('status_pemesanan', 'pending')
            ->first();

        if ($pendingBooking) {
            return redirect()->route('booking.payment', $pendingBooking->id_pemesanan)
                ->with('error', 'Anda masih memiliki pesanan yang belum diselesaikan.');
        }

        // Cek ketersediaan
        if ($kamar->status_kamar == 0) {
            return redirect()->route('dashboard')->with('error', 'Maaf, kamar ini baru saja dipesan orang lain.');
        }

        $kamar->load('tipeKamar');

        // --- LOGIKA BATAS TAMU ---
        $maxTamu = $this->getMaxGuestLimit($kamar->tipeKamar->nama_tipe_kamar);
        // -------------------------

        $fasilitasTersedia = Fasilitas::where('biaya_tambahan', '>', 0)->get();

        // Kirim variabel $maxTamu ke view
        return view('user.booking', compact('kamar', 'fasilitasTersedia', 'maxTamu'));
    }

    public function store(Request $request)
    {
        // 1. Cek pending booking
        $pendingBooking = Pemesanan::where('user_id', Auth::id())
            ->where('status_pemesanan', 'pending')
            ->first();

        if ($pendingBooking) {
            return redirect()->route('booking.payment', $pendingBooking->id_pemesanan)
                ->with('error', 'Transaksi sebelumnya belum selesai.');
        }

        // --- VALIDASI DINAMIS BERDASARKAN TIPE KAMAR ---
        // Kita perlu mengambil data kamar dulu untuk tahu tipe-nya sebelum validasi
        $kamarCheck = Kamar::with('tipeKamar')->find($request->kamar_id);

        $maxTamu = 2; // Default aman
        if ($kamarCheck) {
            $maxTamu = $this->getMaxGuestLimit($kamarCheck->tipeKamar->nama_tipe_kamar);
        }

        $request->validate([
            'kamar_id' => 'required|exists:kamars,id_kamar',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            // Validasi max tamu sesuai tipe kamar
            'jumlah_tamu' => 'required|integer|min:1|max:' . $maxTamu,
            'fasilitas_ids' => 'nullable|array',
            'fasilitas_ids.*' => 'exists:fasilitas,id_fasilitas',
        ], [
            'jumlah_tamu.max' => "Maksimal jumlah tamu untuk tipe kamar ini adalah $maxTamu orang.",
        ]);
        // -----------------------------------------------

        try {
            return DB::transaction(function () use ($request) {

                $kamar = Kamar::where('id_kamar', $request->kamar_id)->lockForUpdate()->first();

                if ($kamar->status_kamar == 0) {
                    throw new \Exception('Maaf, kamar ini baru saja dibooking.');
                }

                $kamar->load('tipeKamar');
                $checkIn = Carbon::parse($request->check_in_date);
                $checkOut = Carbon::parse($request->check_out_date);
                $durasi = $checkIn->diffInDays($checkOut) ?: 1;

                $totalHarga = $kamar->tipeKamar->harga_per_malam * $durasi;

                if ($request->has('fasilitas_ids')) {
                    $fasilitas = Fasilitas::whereIn('id_fasilitas', $request->fasilitas_ids)->get();
                    $totalHarga += $fasilitas->sum('biaya_tambahan');
                }

                $pemesanan = Pemesanan::create([
                    'user_id' => Auth::id(),
                    'kamar_id' => $kamar->id_kamar,
                    'check_in_date' => $request->check_in_date,
                    'check_out_date' => $request->check_out_date,
                    'jumlah_tamu' => $request->jumlah_tamu,
                    'total_harga' => $totalHarga,
                    'status_pemesanan' => 'pending',
                ]);

                if ($request->has('fasilitas_ids')) {
                    $pemesanan->fasilitas()->attach($request->fasilitas_ids);
                }

                $kamar->status_kamar = 0;
                $kamar->save();

                return redirect()->route('booking.payment', $pemesanan->id_pemesanan)
                    ->with('success', 'Pesanan berhasil! Silakan selesaikan pembayaran.');
            });

        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
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

        if ($sisaWaktuDetik <= 0) {
            $pemesanan->update(['status_pemesanan' => 'cancelled']);
            $kamar = $pemesanan->kamar;
            $kamar->status_kamar = 1;
            $kamar->save();
            return redirect()->route('dashboard')->with('error', 'Waktu pembayaran telah habis.');
        }

        return view('user.payment', compact('pemesanan', 'batasWaktu'));
    }

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
        $pemesanan = Pemesanan::with(['kamar.tipeKamar', 'user', 'fasilitas'])->findOrFail($id);

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
