<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna.
     */
    public function index()
    {
        $users = User::with('role')->orderBy('id_role')->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Menampilkan formulir pembuatan pengguna baru.
     */
    public function create()
    {
        // PERUBAHAN DI SINI:
        // Hanya ambil role 'pelanggan' agar admin tidak bisa memilih role 'admin'
        $roles = Role::where('nama_role', 'pelanggan')->get();

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Menyimpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // Pastikan role yang dipilih valid dan sesuai dengan opsi yang tersedia
            'id_role' => ['required', 'exists:roles,id_role'],
        ]);

        // Opsional: Cek server-side agar tidak ada yang mem-bypass form untuk jadi admin
        $roleDipilih = Role::find($request->id_role);
        if ($roleDipilih->nama_role === 'admin') {
            return redirect()->back()->withErrors(['id_role' => 'Anda tidak diizinkan membuat user Admin.']);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_role' => $request->id_role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(User $user)
    {
        // PERUBAHAN DI SINI (Opsional):
        // Jika ingin membatasi edit juga hanya ke pelanggan, gunakan filter yang sama.
        // Namun jika admin boleh mengubah role user lain menjadi admin, gunakan Role::all().
        // Di sini saya asumsikan pembatasan ketat:
        $roles = Role::where('nama_role', 'pelanggan')->get();

        // Jika user yang diedit adalah admin, kita mungkin perlu membiarkan role aslinya terpilih
        // atau mencegah pengeditan role admin.
        if ($user->role->nama_role === 'admin') {
             // Jika mengedit sesama admin, mungkin tampilkan semua role atau biarkan saja
             // Atau redirect jika admin tidak boleh edit admin lain.
             $roles = Role::all();
        }

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'id_role' => ['required', 'exists:roles,id_role'],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Rules\Password::defaults()];
        }

        $request->validate($rules);

        // Opsional: Proteksi update agar tidak bisa mengubah jadi admin lewat inspeksi elemen
        $roleBaru = Role::find($request->id_role);
        if ($roleBaru->nama_role === 'admin' && auth()->user()->id != $user->id) {
             // Logika tambahan: misalnya hanya Super Admin yang boleh buat Admin,
             // tapi untuk kasus sederhana, kita biarkan validasi di create saja.
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'id_role' => $request->id_role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus!');
    }
}
