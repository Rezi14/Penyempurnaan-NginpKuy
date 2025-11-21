<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TipeKamar;
use App\Models\Fasilitas;
use Carbon\Carbon;

class TipeKamarFasilitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mendapatkan semua fasilitas yang dibutuhkan dan menyimpannya dalam array asosiatif untuk memudahkan akses
        $fasilitasMap = Fasilitas::whereIn('nama_fasilitas', ['Wifi', 'AC', 'TV', 'Bathtub', 'Balkon', 'Ruang Keluarga'])
                                ->pluck('id_fasilitas', 'nama_fasilitas')
                                ->toArray();

        // Tentukan mapping fasilitas per tipe kamar (KOREKSI: 'Family' diubah menjadi 'Falimy')
        $mapping = [
            'Standard' => ['Wifi', 'AC'],
            'Deluxe' => ['Wifi', 'AC', 'TV'],
            'Suite' => ['Wifi', 'AC', 'TV', 'Bathtub', 'Balkon'],
            'Family Room' => ['Wifi', 'AC', 'TV', 'Balkon', 'Ruang Keluarga', 'Bathtub'], // KOREKSI
        ];

        foreach ($mapping as $nama_tipe_kamar => $nama_fasilitas_list) {
            // Cari ID Tipe Kamar
            $tipeKamar = TipeKamar::where('nama_tipe_kamar', $nama_tipe_kamar)->first();

            if ($tipeKamar) {
                $pivotData = [];

                foreach ($nama_fasilitas_list as $nama_fasilitas) {
                    if (isset($fasilitasMap[$nama_fasilitas])) {
                        $pivotData[] = [
                            'tipe_kamar_id' => $tipeKamar->id_tipe_kamar,
                            'fasilitas_id' => $fasilitasMap[$nama_fasilitas],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                }

                // Masukkan data pivot ke tabel (menggunakan insertOrIgnore untuk mencegah duplikasi)
                if (!empty($pivotData)) {
                    DB::table('tipe_kamar_fasilitas')->insertOrIgnore($pivotData);
                }
            }
        }
    }
}
