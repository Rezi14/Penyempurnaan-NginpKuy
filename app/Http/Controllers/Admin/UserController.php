<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Import model User
use App\Models\Role; // Import model Role untuk mengelola peran pengguna
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Untuk mengenkripsi password
use Illuminate\Validation\Rule; // Untuk validasi Rule::unique dalam update

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index()
    {
        // Ambil semua pengguna beserta peran mereka
        $users = User::with('role')->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Menampilkan formulir untuk membuat pengguna baru.
     */
    public function create()
    {
        // Ambil semua peran untuk pilihan dropdown
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Menyimpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' akan mencari password_confirmation
            'role_id' => 'required|exists:roles,id',
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Enkripsi password
                'role_id' => $request->role_id,
            ]);

            return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan pengguna: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail pengguna tertentu (opsional, bisa diintegrasikan ke edit).
     */
    public function show(User $user)
    {
        $user->load('role');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Menampilkan formulir untuk mengedit pengguna yang sudah ada.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('role'); // Pastikan peran pengguna dimuat
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Memperbarui pengguna di database.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)], // Email harus unik kecuali untuk pengguna ini sendiri
            'role_id' => 'required|exists:roles,id',
        ];

        // Hanya validasi password jika ada input password baru
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $request->validate($rules);

        try {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $request->role_id,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password); // Enkripsi password baru
            }

            $user->update($userData);

            return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui pengguna: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus pengguna dari database.
     */
    public function destroy(User $user)
    {
        try {
            // Opsional: tambahkan logika untuk mencegah penghapusan admin utama
            // if ($user->isAdmin()) { ... }

            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus pengguna: ' . $e->getMessage());
        }
    }
}