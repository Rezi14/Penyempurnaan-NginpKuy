<?php

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
        // Fasilitas tambahan tidak lagi diperlukan di sini.

        return view('user.booking', compact('kamar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kamar_id' => 'required|exists:kamars,id_kamar',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'jumlah_tamu' => 'required|integer|min:1',
            // Penghapusan validasi fasilitas_ids
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

        // NEW: Hitung biaya tambahan fasilitas default yang termasuk dalam Tipe Kamar
        $fasilitasDefaultIds = [];
        $biayaTambahanTotal = 0;

        foreach ($kamar->tipeKamar->fasilitas as $fasilitas) {
            $biayaTambahanTotal += $fasilitas->biaya_tambahan;
            $fasilitasDefaultIds[] = $fasilitas->id_fasilitas;
        }

        // Tambahkan biaya fasilitas default satu kali (biaya fasilitas TIDAK dikalikan durasi menginap)
        $totalHarga += $biayaTambahanTotal;

        $pemesanan = Pemesanan::create([
            'user_id' => Auth::id(),
            'kamar_id' => $kamar->id_kamar,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'jumlah_tamu' => $request->jumlah_tamu,
            'total_harga' => $totalHarga,
            'status_pemesanan' => 'pending',
        ]);

        // NEW: Lampirkan fasilitas default ke pemesanan secara otomatis
        // Ini memastikan histori pemesanan tetap mencatat fasilitas apa yang didapatkan
        if (!empty($fasilitasDefaultIds)) {
            $pemesanan->fasilitas()->attach($fasilitasDefaultIds);
        }

        return redirect()->route('dashboard')->with('success', 'Pemesanan kamar berhasil dibuat! Total harga: Rp ' . number_format($totalHarga, 2, ',', '.'));
    }
}
