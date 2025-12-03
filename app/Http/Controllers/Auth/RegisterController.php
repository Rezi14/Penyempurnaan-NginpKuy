<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Menampilkan formulir pendaftaran.
     */
    public function showRegistrationForm(): View
    {
        return view('Auth.register');
    }

    /**
     * Menangani proses pendaftaran pengguna baru.
     */
    public function register(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // 2. Tentukan ID Role Default (Pelanggan)
        // Menggunakan firstOrCreate untuk mempersingkat logika "Cari atau Buat"
        $pelangganRole = Role::firstOrCreate(
            ['nama_role' => 'pelanggan'], // Kondisi pencarian
            ['deskripsi' => 'Pengguna biasa yang dapat memesan kamar.'] // Data jika perlu dibuat baru
        );

        // 3. Buat User Baru
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // Hashing eksplisit untuk keamanan
            'id_role'  => $pelangganRole->id_role,
        ]);

        // Kirim event registered (berguna jika nanti mengaktifkan verifikasi email)
        event(new Registered($user));

        // 4. Login User Secara Otomatis
        Auth::login($user);

        // 5. Redirect Setelah Pendaftaran Berhasil
        // Mengarahkan ke dashboard pengguna
        return redirect()->intended('/dashboard')
            ->with('success', 'Pendaftaran berhasil! Selamat datang.');
    }
}
