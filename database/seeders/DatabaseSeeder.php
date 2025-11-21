<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Urutan pemanggilan seeder sangat penting:
        // 1. Pastikan TipeKamar sudah ada (jika Anda memiliki TipeKamarSeeder)
        // 2. Masukkan Fasilitas Default
        // 3. Hubungkan Tipe Kamar dan Fasilitas

        $this->call([
            // TipeKamarSeeder::class, // Pastikan TipeKamar Anda sudah ada
            FasilitasDefaultSeeder::class, // NEW: Masukkan fasilitas dasar
            TipeKamarFasilitasSeeder::class, // NEW: Hubungkan fasilitas ke tipe kamar
            // UserSeeder::class, // Seeder lainnya
        ]);

        // \App\Models\User::factory(10)->create();
    }
}
