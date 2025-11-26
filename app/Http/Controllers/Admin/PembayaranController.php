<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    /**
     * Menampilkan riwayat pemesanan.
     * Menggantikan fitur riwayat transaksi manual karena pembayaran dilakukan di awal oleh User.
     * * @return \Illuminate\View\View
     */
    public function index()
    {
        // Mengambil data pemesanan yang statusnya BUKAN 'pending'.
        // Status 'pending' berarti belum dibayar/dibatalkan user, jadi belum masuk riwayat.
        // Data diurutkan berdasarkan waktu pembuatan (waktu transaksi).
        $riwayatPemesanan = Pemesanan::with(['user', 'kamar.tipeKamar', 'fasilitas'])
                                    ->where('status_pemesanan', '!=', 'pending')
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        // Menggunakan view yang sama (transaksi.blade.php) namun dengan data yang disesuaikan
        return view('admin.riwayat.transaksi', compact('riwayatPemesanan'));
    }
}
