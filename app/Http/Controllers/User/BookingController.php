<?php

namespace App\Http\Controllers\User;

use Illuminate\Database\Query\Builder;
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
        // if ($kamar->status_kamar == 0) {
        //     return redirect()->route('dashboard')->with('error', 'Maaf, kamar ini baru saja dipesan orang lain.');
        // }

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
        // 1. Cek pending booking user saat ini (biar ga numpuk)
        $pendingBooking = Pemesanan::where('user_id', Auth::id())
            ->where('status_pemesanan', 'pending')
            ->first();

        if ($pendingBooking) {
            return redirect()->route('booking.payment', $pendingBooking->id_pemesanan)
                ->with('error', 'Selesaikan pembayaran transaksi sebelumnya.');
        }

        $request->validate([
            'kamar_id' => 'required|exists:kamars,id_kamar',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'jumlah_tamu' => 'required|integer|min:1',
            'fasilitas_ids' => 'nullable|array',
        ]);

        $checkIn = $request->check_in_date;
        $checkOut = $request->check_out_date;

        try {
            return DB::transaction(function () use ($request, $checkIn, $checkOut) {

                // --- LOGIKA BARU: Cek Overlap Tanggal ---
                // Kita cari apakah ada pesanan lain di kamar ini yang tanggalnya bertabrakan
                // Dan statusnya TIDAK 'cancelled' (artinya confirmed, pending, atau checked_in)
                $isBooked = Pemesanan::where('kamar_id', $request->kamar_id)
                    ->where('status_pemesanan', '!=', 'cancelled')
                    ->where('status_pemesanan', '!=', 'checked_out') // Opsional: anggap checkout sudah kosong
                    ->where(function ($query) use ($checkIn, $checkOut) {
                        $query->where(function ($q) use ($checkIn, $checkOut) {
                            // Kasus: Tanggal Check-in baru ada di antara periode booking orang lain
                            $q->where('check_in_date', '<', $checkOut)
                                ->where('check_out_date', '>', $checkIn);
                        });
                    })
                    ->lockForUpdate() // Mencegah race condition saat query bersamaan
                    ->exists();

                if ($isBooked) {
                    // Redirect kembali dengan error jika tanggal sudah terisi
                    return back()->with('error', 'Maaf, kamar tidak tersedia pada tanggal yang dipilih.');
                }
                // ----------------------------------------

                $kamar = Kamar::with('tipeKamar')->findOrFail($request->kamar_id);

                // Hitung durasi & harga
                $in = Carbon::parse($checkIn);
                $out = Carbon::parse($checkOut);
                $durasi = $in->diffInDays($out) ?: 1;

                $totalHarga = $kamar->tipeKamar->harga_per_malam * $durasi;

                if ($request->has('fasilitas_ids')) {
                    $fasilitas = Fasilitas::whereIn('id_fasilitas', $request->fasilitas_ids)->get();
                    $totalHarga += $fasilitas->sum('biaya_tambahan');
                }

                // Buat Pemesanan
                $pemesanan = Pemesanan::create([
                    'user_id' => Auth::id(),
                    'kamar_id' => $kamar->id_kamar,
                    'check_in_date' => $checkIn,
                    'check_out_date' => $checkOut,
                    'jumlah_tamu' => $request->jumlah_tamu,
                    'total_harga' => $totalHarga,
                    'status_pemesanan' => 'pending',
                ]);

                if ($request->has('fasilitas_ids')) {
                    $pemesanan->fasilitas()->attach($request->fasilitas_ids);
                }

                // HAPUS bagian ini: $kamar->status_kamar = 0; $kamar->save();
                // Kita tidak lagi mengunci kamar secara global (hard lock).

                return redirect()->route('booking.payment', $pemesanan->id_pemesanan)
                    ->with('success', 'Pesanan berhasil dibuat! Silakan bayar.');
            });

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
            // $kamar = $pemesanan->kamar;
            // $kamar->status_kamar = 1;
            // $kamar->save();
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
