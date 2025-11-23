<?php

namespace App\Http\Controllers\Admin; // Perhatikan: namespace yang benar

use App\Http\Controllers\Controller;
use App\Models\Pemesanan; // Import model Pemesanan
use Illuminate\Http\Request;
use Carbon\Carbon; // Import Carbon

class PembayaranController extends Controller
{
    /**
     * Menampilkan halaman pembayaran untuk pemesanan tertentu.
     * @param Pemesanan $pemesanan
     * @return \Illuminate->View\View|\Illuminate->Http->RedirectResponse
     */
    public function show(Pemesanan $pemesanan)
    {
        if ($pemesanan->status_pemesanan !== 'checked_out') {
            return redirect()->route('admin.pemesanans.index')->with('error', 'Pemesanan ini tidak dalam status yang tepat untuk pembayaran.');
        }

        $pemesanan->load(['user', 'kamar.tipeKamar', 'fasilitas']);

        return view('admin.pembayaran.pembayaran', compact('pemesanan'));
    }

    /**
     * Memproses konfirmasi pembayaran.
     * @param Request $request
     * @param Pemesanan $pemesanan
     * @return \Illuminate->Http->RedirectResponse
     */
    public function process(Request $request, Pemesanan $pemesanan)
    {
        $request->validate([
            'payment_method' => 'required|string|in:qris,tunai',
        ]);

        try {
            if ($pemesanan->status_pemesanan !== 'checked_out') {
                return redirect()->back()->with('error', 'Pemesanan ini tidak dalam status yang tepat untuk pembayaran.');
            }

            $pemesanan->status_pemesanan = 'paid';
            $pemesanan->save();

            return redirect()->route('admin.dashboard')->with('success', 'Pembayaran berhasil dikonfirmasi. Pemesanan telah dipindahkan ke riwayat transaksi.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan riwayat transaksi (pemesanan yang sudah paid atau cancelled).
     * @return \Illuminate->View\View
     */
    public function history() // <-- Metode baru
    {
        $riwayatTransaksi = Pemesanan::with(['user', 'kamar.tipeKamar', 'fasilitas'])
                                    ->whereIn('status_pemesanan', ['paid', 'cancelled']) // Ambil yang paid atau cancelled
                                    ->orderBy('check_out_date', 'desc') // Urutkan berdasarkan tanggal check-out terbaru
                                    ->get();

        return view('admin.riwayat.transaksi', compact('riwayatTransaksi'));
    }
}