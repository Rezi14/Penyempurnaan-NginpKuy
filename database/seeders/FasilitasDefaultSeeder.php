<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FasilitasDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fasilitas = [
            ['nama_fasilitas' => 'Wifi', 'deskripsi' => 'Akses internet nirkabel gratis.', 'biaya_tambahan' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_fasilitas' => 'AC', 'deskripsi' => 'Pendingin ruangan.', 'biaya_tambahan' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_fasilitas' => 'TV', 'deskripsi' => 'Televisi layar datar dengan saluran lokal dan internasional.', 'biaya_tambahan' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_fasilitas' => 'Bathtub', 'deskripsi' => 'Bak mandi pribadi.', 'biaya_tambahan' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_fasilitas' => 'Balkon', 'deskripsi' => 'Balkon pribadi dengan pemandangan.', 'biaya_tambahan' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_fasilitas' => 'Ruang Keluarga', 'deskripsi' => 'Area ruang keluarga terpisah di dalam kamar.', 'biaya_tambahan' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Anda bisa menambahkan fasilitas lain dengan biaya > 0 di sini jika diperlukan,
            // contoh: ['nama_fasilitas' => 'Sarapan Mewah', 'deskripsi' => 'Sarapan ala carte', 'biaya_tambahan' => 50000, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        // Gunakan insertOrIgnore untuk menghindari duplikasi jika seeder dijalankan lebih dari sekali
        DB::table('fasilitas')->insertOrIgnore($fasilitas);
    }
}
