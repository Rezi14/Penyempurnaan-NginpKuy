<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role; // Import model Role

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat role 'admin' jika belum ada
        Role::firstOrCreate(
            ['nama_role' => 'admin'],
            ['deskripsi' => 'Pengguna dengan hak akses penuh untuk mengelola sistem.']
        );

        // Buat role 'pelanggan' jika belum ada
        Role::firstOrCreate(
            ['nama_role' => 'pelanggan'],
            ['deskripsi' => 'Pengguna biasa yang dapat memesan kamar.']
        );
    }
}