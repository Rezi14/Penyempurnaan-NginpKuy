<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role; // Import Role model
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role 'admin' ada sebelum membuat user admin
        $adminRole = Role::where('nama_role', 'admin')->first();
        if (!$adminRole) {
            $this->command->error('Role "admin" tidak ditemukan. Jalankan RoleSeeder terlebih dahulu!');
            return;
        }

        User::firstOrCreate(
            ['email' => 'hoshi1014@gmail.com'], // Cek berdasarkan email
            [
                'name' => 'rz',
                'password' => Hash::make('123'), // Password untuk admin
                'id_role' => $adminRole->id_role, // <<< TETAPKAN ID ROLE ADMIN DI SINI
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Pengguna Admin telah berhasil di-seed!');
    }
}