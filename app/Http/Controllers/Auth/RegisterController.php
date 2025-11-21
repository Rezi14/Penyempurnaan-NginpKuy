<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role; // Import model Role

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('Auth.register');
    }

    public function register(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Tentukan ID Role Default (Pelanggan)
        // Cari role 'pelanggan' berdasarkan nama
        $pelangganRole = Role::where('nama_role', 'pelanggan')->first();

        // Jika role 'pelanggan' tidak ditemukan, Anda bisa:
        // a. Buat role 'pelanggan' secara otomatis (disarankan untuk seeding awal)
        // b. Lempar error atau redirect dengan pesan.
        if (!$pelangganRole) {
            // Contoh: Jika tidak ditemukan, buat role 'pelanggan'
            $pelangganRole = Role::create([
                'nama_role' => 'pelanggan',
                'deskripsi' => 'Pengguna biasa yang dapat memesan kamar.',
            ]);
            // Atau, jika Anda tidak ingin membuat otomatis:
            // return back()->withInput()->withErrors(['role' => 'Role "pelanggan" tidak ditemukan. Harap hubungi administrator.']);
        }

        // 3. Buat User Baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Password akan di-hash otomatis karena casting di model User
            'id_role' => $pelangganRole->id_role, // Tetapkan role 'pelanggan'
        ]);

        // 4. Login User Setelah Pendaftaran (Opsional)
        Auth::login($user);

        // 5. Redirect Setelah Pendaftaran Berhasil
        return redirect()->intended('/dashboard')->with('success', 'Pendaftaran berhasil! Selamat datang.');
    }
}
