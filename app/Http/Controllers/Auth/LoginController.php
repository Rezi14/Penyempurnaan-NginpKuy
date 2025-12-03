<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Tampilkan formulir login.
     */
    public function showLoginForm(): View
    {
        return view('Auth.login');
    }

    /**
     * Tangani permintaan login.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email_or_name' => 'required|string',
            'password'      => 'required|string',
        ]);

        $loginField = $request->input('email_or_name');
        $password   = $request->input('password');

        // Tentukan apakah input adalah email atau username (name)
        $fieldType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (Auth::attempt([$fieldType => $loginField, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Logika redirect berdasarkan role
            // Pastikan relasi 'role' ada di model User dan kolom 'nama_role' ada di tabel roles
            if ($user->role && $user->role->nama_role === 'admin') {
                return redirect()->intended('/admin/dashboard')
                    ->with('success', 'Selamat datang, Roomify!');
            }

            return redirect()->intended('/')
                ->with('success', 'Berhasil login! Selamat datang.');
        }

        return back()->withErrors([
            'email_or_name' => 'Kombinasi email/nama pengguna dan password tidak valid.',
        ])->onlyInput('email_or_name');
    }

    /**
     * Tangani permintaan logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout(); // Logout user dari aplikasi

        $request->session()->invalidate(); // Invalidasi session saat ini
        $request->session()->regenerateToken(); // Regenerasi token CSRF

        return redirect('/')
            ->with('success', 'Anda telah logout.');
    }
}
