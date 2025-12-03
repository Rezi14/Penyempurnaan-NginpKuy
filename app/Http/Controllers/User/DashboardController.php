<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

// Models
use App\Models\Kamar;
use App\Models\TipeKamar;
use App\Models\Fasilitas;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard user dengan filter pencarian kamar.
     */
    public function index(Request $request)
    {
        // 1. Ambil data untuk dropdown filter
        $tipeKamarList = TipeKamar::all();
        $fasilitasList = Fasilitas::where('biaya_tambahan', '=', 0)->get();

        // 2. Query Pencarian Kamar
        $kamarsTersedia = Kamar::with(['tipeKamar.fasilitas'])
            // Pastikan hanya menampilkan kamar yang statusnya 'Aktif' (bukan sedang maintenance)
            ->where('status_kamar', 1)
            ->when($request->filled(['check_in', 'check_out']), function ($query) use ($request) {
                $checkIn = $request->check_in;
                $checkOut = $request->check_out;

                $query->whereDoesntHave('pemesanans', function ($q) use ($checkIn, $checkOut) {
                    $q->where('status_pemesanan', '!=', 'cancelled')
                        ->where(function ($sub) use ($checkIn, $checkOut) {
                            $sub->whereBetween('check_in_date', [$checkIn, $checkOut])
                                ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                                ->orWhere(function ($overlap) use ($checkIn, $checkOut) {
                                    $overlap->where('check_in_date', '<=', $checkIn)
                                        ->where('check_out_date', '>=', $checkOut);
                                });
                        });
                });
            })

            // Filter: Tipe Kamar
            ->when($request->filled('tipe_kamar'), function (Builder $query) use ($request) {
                $query->where('id_tipe_kamar', $request->tipe_kamar);
            })

            // Filter: Range Harga (Cek di tabel relasi tipeKamar)
            ->when($request->filled('harga_min'), function (Builder $query) use ($request) {
                $query->whereHas('tipeKamar', function (Builder $q) use ($request) {
                    $q->where('harga_per_malam', '>=', $request->harga_min);
                });
            })
            ->when($request->filled('harga_max'), function (Builder $query) use ($request) {
                $query->whereHas('tipeKamar', function (Builder $q) use ($request) {
                    $q->where('harga_per_malam', '<=', $request->harga_max);
                });
            })

            // Filter: Fasilitas (Harus memiliki SEMUA fasilitas yang dipilih)
            ->when($request->filled('fasilitas') && is_array($request->fasilitas), function (Builder $query) use ($request) {
                foreach ($request->fasilitas as $fasilitasId) {
                    $query->whereHas('tipeKamar.fasilitas', function (Builder $q) use ($fasilitasId) {
                        $q->where('fasilitas.id_fasilitas', $fasilitasId);
                    });
                }
            })

            ->orderBy('nomor_kamar', 'asc')
            ->get();

        // 3. Statistik Dashboard
        $totalKamar = Kamar::count();
        $tersedia = Kamar::where('status_kamar', 1)->count();
        $terisi = Kamar::where('status_kamar', 0)->count();
        $totalTipe = TipeKamar::count();

        return view('user.dashboard', [
            'kamarsTersedia' => $kamarsTersedia,
            'totalKamar' => $totalKamar,
            'tersedia' => $tersedia,
            'terisi' => $terisi,
            'totalTipe' => $totalTipe,
            'user' => Auth::user(),
            // Data filter
            'tipeKamarList' => $tipeKamarList,
            'fasilitasList' => $fasilitasList,
        ]);
    }
}
