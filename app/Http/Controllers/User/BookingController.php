<?php
// app/Http/Controllers/User/BookingController.php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\Kamar;
use App\Models\Pemesanan;
use App\Models\Fasilitas; // Model Fasilitas masih dibutuhkan untuk perhitungan harga
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Pastikan Carbon diimpor

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showBookingForm(Kamar $kamar)
    {
        // Eager load tipeKamar dan fasilitas default-nya
        $kamar->load('tipeKamar.fasilitas');

        // NEW: Ambil semua fasilitas yang tersedia (untuk opsi tambahan)
        $allFasilitas = Fasilitas::all();

        return view('user.booking', compact('kamar', 'allFasilitas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kamar_id' => 'required|exists:kamars,id_kamar',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'jumlah_tamu' => 'required|integer|min:1',
            // NEW: Validasi untuk fasilitas tambahan
            'fasilitas_tambahan' => 'nullable|array',
            'fasilitas_tambahan.*' => 'exists:fasilitas,id_fasilitas',
        ]);

        $kamar = Kamar::findOrFail($request->kamar_id);
        // Pastikan relasi tipeKamar dan fasilitas dimuat untuk perhitungan
        $kamar->load('tipeKamar.fasilitas');

        $checkIn = Carbon::parse($request->check_in_date);
        $checkOut = Carbon::parse($request->check_out_date);
        $durasiMenginap = $checkIn->diffInDays($checkOut);
        if ($durasiMenginap == 0) {
            $durasiMenginap = 1;
        }

        $hargaPerMalam = $kamar->tipeKamar->harga_per_malam;
        $totalHarga = $hargaPerMalam * $durasiMenginap;

        // Collect all facility IDs (default and selected additional)
        $allFacilityIds = [];

        // 1. Hitung biaya fasilitas default (sudah ada)
        $biayaTambahanDefault = 0;
        foreach ($kamar->tipeKamar->fasilitas as $fasilitas) {
            $biayaTambahanDefault += $fasilitas->biaya_tambahan;
            $allFacilityIds[] = $fasilitas->id_fasilitas;
        }
        $totalHarga += $biayaTambahanDefault;

        // 2. NEW: Hitung biaya fasilitas tambahan yang dipilih pengguna
        $selectedAdditionalFasilitasIds = $request->input('fasilitas_tambahan', []);
        $additionalFasilitas = Fasilitas::whereIn('id_fasilitas', $selectedAdditionalFasilitasIds)->get();

        $biayaTambahanSelected = 0;
        foreach ($additionalFasilitas as $fasilitas) {
            $biayaTambahanSelected += $fasilitas->biaya_tambahan;
            $allFacilityIds[] = $fasilitas->id_fasilitas; // Tambahkan ID ke daftar total
        }
        $totalHarga += $biayaTambahanSelected;

        // Pastikan tidak ada ID fasilitas yang sama terlampir dua kali
        $allUniqueFacilityIds = array_unique($allFacilityIds);

        $pemesanan = Pemesanan::create([
            'user_id' => Auth::id(),
            'kamar_id' => $kamar->id_kamar,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'jumlah_tamu' => $request->jumlah_tamu,
            'total_harga' => $totalHarga,
            'status_pemesanan' => 'pending',
        ]);

        // NEW: Lampirkan SEMUA fasilitas (default + tambahan) ke pemesanan
        if (!empty($allUniqueFacilityIds)) {
            $pemesanan->fasilitas()->attach($allUniqueFacilityIds);
        }

        return redirect()->route('dashboard')->with('success', 'Pemesanan kamar berhasil dibuat! Total harga: Rp ' . number_format($totalHarga, 2, ',', '.'));
    }
}
