<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth; // Pastikan namespace ini benar

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Pastikan Auth facade diimport
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Pastikan model User diimport

// Sesuaikan baris ini berdasarkan solusi Controller.php Anda
use App\Http\Controllers\Controller; // Jika Anda sudah memperbaiki Controller.php
// ATAU: use Illuminate\Routing\Controller; // Jika Anda menggunakan solusi cepat (tanpa mengubah Controller.php)


class LoginController extends Controller
{
    /**
     * Tampilkan formulir login.
     */
    public function showLoginForm()
    {
        return view('Auth.login'); // Akan mencari resources/views/login.blade.php
    }

    /**
     * Tangani permintaan login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email_or_name' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = $request->input('email_or_name');
        $password = $request->input('password');

        $fieldType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (Auth::attempt([$fieldType => $loginField, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Logika redirect berdasarkan role
            $user = Auth::user();
            if ($user->role && $user->role->nama_role === 'admin') {
                return redirect()->intended('/dashboard-admin')->with('success', 'Selamat datang, Roomify!');
            } else {
                return redirect()->intended('/')->with('success', 'Berhasil login! Selamat datang.');
            }
        }

        return back()->withErrors([
            'email_or_name' => 'Kombinasi email/nama pengguna dan password tidak valid.',
        ])->onlyInput('email_or_name');
    }

    /**
     * Tangani permintaan logout.
     * Ini adalah method yang harus Anda pastikan ada dan benar.
     */
    public function logout(Request $request)
    {
        Auth::logout(); // Logout user dari aplikasi

        $request->session()->invalidate(); // Invalidasi session saat ini
        $request->session()->regenerateToken(); // Regenerasi token CSRF

        return redirect('/')->with('success', 'Anda telah logout.'); // Redirect ke halaman utama
    }
}
