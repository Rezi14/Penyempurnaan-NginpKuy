<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kamar;
use App\Models\Pemesanan;
use App\Models\User;
use Carbon\Carbon; // Import Carbon

class DashboardAdminController extends Controller
{
    public function index()
    {
        $totalKamar = Kamar::count();
        $totalPemesanan = Pemesanan::count(); // Total semua pemesanan
        $totalPengguna = User::count();

        // Ambil pemesanan yang statusnya 'checked_in' dan belum check-out
        $pelangganCheckin = Pemesanan::with(['user', 'kamar.tipeKamar', 'fasilitas']) // Muat relasi fasilitas juga
            ->where('status_pemesanan', 'checked_in')
            ->whereDate('check_in_date', '<=', Carbon::today()) // Pastikan sudah check-in
            ->whereDate('check_out_date', '>=', Carbon::today()) // Belum check-out
            ->get();

        return view('admin.dashboard', compact('totalKamar', 'totalPemesanan', 'totalPengguna', 'pelangganCheckin'));
    }
}