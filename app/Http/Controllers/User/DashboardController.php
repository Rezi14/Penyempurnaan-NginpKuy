<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kamar;
use App\Models\TipeKamar;
use App\Models\Fasilitas;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        
        // TAMBAHAN: list tipe & fasilitas untuk dropdown
        $tipeKamarList = TipeKamar::all();
        $fasilitasList = Fasilitas::all();

        // Query utama: cari kamar tersedia, tapi filter harga berada di table tipe_kamars
        $kamarsTersedia = Kamar::with('tipeKamar.fasilitas')
            ->where('status_kamar', true)

            // filter tipe kamar langsung di kamars (id_tipe_kamar di table kamars)
            ->when($request->tipe_kamar, function ($q) use ($request) {
                $q->where('id_tipe_kamar', $request->tipe_kamar);
            })

            // FILTER RANGE HARGA -> diterapkan ke relasi tipeKamar.harga_per_malam
            ->when($request->harga_min, function ($q) use ($request) {
                $min = $request->harga_min;
                $q->whereHas('tipeKamar', function ($sub) use ($min) {
                    // gunakan nama kolom yang benar di table tipe_kamars
                    $sub->where('harga_per_malam', '>=', $min);
                });
            })
            ->when($request->harga_max, function ($q) use ($request) {
                $max = $request->harga_max;
                $q->whereHas('tipeKamar', function ($sub) use ($max) {
                    $sub->where('harga_per_malam', '<=', $max);
                });
            })

                                // Filter berdasarkan fasilitas (wajib punya semua fasilitas terpilih)
                                ->when($request->fasilitas, function ($q) use ($request) {

                                    foreach ($request->fasilitas as $fasID) {
                                        // AND logic: setiap fasilitas wajib ada
                                        $q->whereHas('tipeKamar.fasilitas', function ($sub) use ($fasID) {
                                            $sub->where('fasilitas.id_fasilitas', $fasID);
                                        });
                                    }

                                })



                                ->orderBy('nomor_kamar', 'asc') // Filter kamar yang statusnya true
                                ->get();

        // KODE LAMA — TIDAK DIUBAH
        $totalKamar = Kamar::count();
        $tersedia = Kamar::where('status_kamar', true)->count();
        $terisi = Kamar::where('status_kamar', false)->count();
        $totalTipe = \App\Models\TipeKamar::count();

        return view('user.dashboard', [
            'kamarsTersedia' => $kamarsTersedia,
            'totalKamar' => $totalKamar,
            'user' => Auth::user(),
            'tersedia' => $tersedia,
            'terisi' => $terisi,
            'totalTipe' => $totalTipe,

            // TAMBAHAN: kirim ke view
            'tipeKamarList' => $tipeKamarList,
            'fasilitasList' => $fasilitasList,
        ]);
    }
}
