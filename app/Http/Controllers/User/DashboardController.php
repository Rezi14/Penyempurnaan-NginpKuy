<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kamar; // Import model Kamar

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil data kamar yang status_kamar-nya true (tersedia)
        // Dengan eager loading untuk relasi tipeKamar dan fasilitas default-nya
        $kamarsTersedia = Kamar::with('tipeKamar.fasilitas')
                                ->where('status_kamar', true)
                                ->orderBy('nomor_kamar', 'asc') // Filter kamar yang statusnya true
                                ->get();

        // Mengirim data kamar yang tersedia ke view dashboard
        return view('user.dashboard', [
            'kamarsTersedia' => $kamarsTersedia,
            'user' => Auth::user() // Mengirim data user ke view juga
        ]);
    }
}
