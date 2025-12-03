<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Pemesanan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * @method void middleware(string|array $middleware, array $options = [])
 */

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Menampilkan form booking.
     */
    public function showBookingForm(Kamar $kamar): View|RedirectResponse
    {
        // 1. Cek pending booking user saat ini
        $pendingBooking = Pemesanan::where('user_id', Auth::id())
            ->where('status_pemesanan', 'pending')
            ->first();

        if ($pendingBooking) {
            return redirect()->route('booking.payment', $pendingBooking->id_pemesanan)
                ->with('error', 'Anda masih memiliki pesanan yang belum diselesaikan.');
        }

        $kamar->load('tipeKamar');

        // Ambil kapasitas maksimal dari relasi tipe kamar
        $maxTamu = $kamar->tipeKamar->kapasitas;

        $fasilitasTersedia = Fasilitas::where('biaya_tambahan', '>', 0)->get();

        return view('user.booking', compact('kamar', 'fasilitasTersedia', 'maxTamu'));
    }

    /**
     * Memproses penyimpanan pemesanan baru.
     */
    public function store(Request $request): RedirectResponse
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
            'kamar_id'       => ['required', 'exists:kamars,id_kamar'],
            'check_in_date'  => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'jumlah_tamu'    => ['required', 'integer', 'min:1'],
            'fasilitas_ids'  => ['nullable', 'array'],
        ]);

        $checkIn  = $request->check_in_date;
        $checkOut = $request->check_out_date;

        try {
            return DB::transaction(function () use ($request, $checkIn, $checkOut) {

                // --- LOGIKA: Cek Overlap Tanggal ---
                // Cek apakah ada pesanan lain di kamar ini yang tanggalnya bertabrakan
                $isBooked = Pemesanan::where('kamar_id', $request->kamar_id)
                    ->where('status_pemesanan', '!=', 'cancelled')
                    ->where('status_pemesanan', '!=', 'checked_out')
                    ->where(function ($query) use ($checkIn, $checkOut) {
                        $query->where(function ($q) use ($checkIn, $checkOut) {
                            // Kasus: Tanggal Check-in baru ada di antara periode booking orang lain
                            $q->where('check_in_date', '<', $checkOut)
                              ->where('check_out_date', '>', $checkIn);
                        });
                    })
                    ->lockForUpdate() // Mencegah race condition
                    ->exists();

                if ($isBooked) {
                    return back()->with('error', 'Maaf, kamar tidak tersedia pada tanggal yang dipilih.');
                }

                $kamar = Kamar::with('tipeKamar')->findOrFail($request->kamar_id);

                // Hitung durasi & harga
                $in     = Carbon::parse($checkIn);
                $out    = Carbon::parse($checkOut);
                $durasi = $in->diffInDays($out) ?: 1;

                $totalHarga = $kamar->tipeKamar->harga_per_malam * $durasi;

                if ($request->has('fasilitas_ids')) {
                    $fasilitas  = Fasilitas::whereIn('id_fasilitas', $request->fasilitas_ids)->get();
                    $totalHarga += $fasilitas->sum('biaya_tambahan');
                }

                // Buat Pemesanan
                $pemesanan = Pemesanan::create([
                    'user_id'          => Auth::id(),
                    'kamar_id'         => $kamar->id_kamar,
                    'check_in_date'    => $checkIn,
                    'check_out_date'   => $checkOut,
                    'jumlah_tamu'      => $request->jumlah_tamu,
                    'total_harga'      => $totalHarga,
                    'status_pemesanan' => 'pending',
                ]);

                if ($request->has('fasilitas_ids')) {
                    $pemesanan->fasilitas()->attach($request->fasilitas_ids);
                }

                return redirect()->route('booking.payment', $pemesanan->id_pemesanan)
                    ->with('success', 'Pesanan berhasil dibuat! Silakan bayar.');
            });

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman pembayaran.
     */
    public function showPayment($id): View|RedirectResponse
    {
        $pemesanan = Pemesanan::with(['kamar.tipeKamar'])->findOrFail($id);

        // Pastikan user hanya bisa melihat pembayarannya sendiri
        if ($pemesanan->user_id !== Auth::id()) {
            abort(403);
        }

        if ($pemesanan->status_pemesanan !== 'pending') {
            return redirect()->route('dashboard');
        }

        $waktuDibuat    = Carbon::parse($pemesanan->created_at);
        $batasWaktu     = $waktuDibuat->addMinutes(10);
        $sisaWaktuDetik = Carbon::now()->diffInSeconds($batasWaktu, false);

        // Jika waktu habis, batalkan otomatis
        if ($sisaWaktuDetik <= 0) {
            $pemesanan->update(['status_pemesanan' => 'cancelled']);

            // Mengembalikan status kamar (jika diperlukan oleh logika lain)
            $kamar = $pemesanan->kamar;
            if ($kamar) {
                $kamar->status_kamar = 1;
                $kamar->save();
            }

            return redirect()->route('dashboard')->with('error', 'Waktu pembayaran telah habis.');
        }

        return view('user.payment', compact('pemesanan', 'batasWaktu'));
    }

    /**
     * Cek status pembayaran secara real-time (Ajax).
     */
    public function checkPaymentStatus($id): JsonResponse
    {
        $pemesanan = Pemesanan::findOrFail($id);

        if ($pemesanan->status_pemesanan == 'confirmed') {
            return response()->json(['status' => 'success']);
        }

        $batasWaktu = Carbon::parse($pemesanan->created_at)->addMinutes(10);

        if (Carbon::now()->greaterThan($batasWaktu)) {
            $pemesanan->update(['status_pemesanan' => 'cancelled']);

            $kamar = $pemesanan->kamar;
            if ($kamar) {
                $kamar->status_kamar = 1;
                $kamar->save();
            }

            return response()->json(['status' => 'expired']);
        }

        if ($pemesanan->status_pemesanan == 'cancelled') {
            return response()->json(['status' => 'expired']);
        }

        return response()->json(['status' => 'pending']);
    }

    /**
     * Membatalkan booking secara manual oleh user.
     */
    public function cancelBooking($id): RedirectResponse
    {
        $pemesanan = Pemesanan::findOrFail($id);

        if ($pemesanan->user_id == Auth::id() && $pemesanan->status_pemesanan == 'pending') {
            $pemesanan->update(['status_pemesanan' => 'cancelled']);

            return redirect()->route('dashboard')->with('success', 'Pesanan dibatalkan.');
        }

        return back();
    }

    /**
     * Menampilkan detail booking.
     */
    public function detail($id): View
    {
        $pemesanan = Pemesanan::with(['kamar.tipeKamar', 'user', 'fasilitas'])->findOrFail($id);

        if (Auth::id() !== $pemesanan->user_id) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI.');
        }

        return view('user.pages.order-detail', compact('pemesanan'));
    }

    /**
     * Simulasi sukses bayar (Dev Only).
     */
    public function simulatePaymentSuccess($id): RedirectResponse|string
    {
        $pemesanan = Pemesanan::with('kamar')->findOrFail($id);

        if ($pemesanan->status_pemesanan == 'pending') {
            $pemesanan->update(['status_pemesanan' => 'confirmed']);
            return redirect()->route('dashboard')->with('success', 'Pembayaran Berhasil! Kamar Berhasil Dipesan.');
        }

        return "Pesanan tidak valid atau sudah diproses.";
    }
}
