<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('user.pages.contact');
    }

    public function send(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // 2. Logika Kirim Email / Simpan ke Database
        // (Di sini Anda bisa menambahkan kode Mail::to(...) atau menyimpan ke tabel pesan)
        // Untuk saat ini, kita simpan pesan sukses ke session saja.

        return back()->with('success', 'Terima kasih! Pesan Anda telah kami terima. Tim kami akan segera menghubungi Anda.');
    }
}
