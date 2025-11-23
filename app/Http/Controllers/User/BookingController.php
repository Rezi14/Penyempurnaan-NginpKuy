<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\Kamar;
use App\Models\Pemesanan;
use App\Models\Fasilitas; // Import model Fasilitas baru
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
        $kamar->load('tipeKamar');
        $fasilitasTersedia = Fasilitas::all(); // Ambil semua fasilitas yang tersedia

        return view('user.booking', compact('kamar', 'fasilitasTersedia')); // Kirim fasilitas ke view
    }

    public function store(Request $request)
    {
        $request->validate([
            'kamar_id' => 'required|exists:kamars,id_kamar',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'jumlah_tamu' => 'required|integer|min:1',
            'fasilitas_ids' => 'nullable|array', // Validasi fasilitas yang dipilih (array opsional)
            'fasilitas_ids.*' => 'exists:fasilitas,id_fasilitas', // Pastikan setiap ID fasilitas ada di tabel fasilitas
        ]);

        $kamar = Kamar::findOrFail($request->kamar_id);
        $kamar->load('tipeKamar');

        $checkIn = Carbon::parse($request->check_in_date);
        $checkOut = Carbon::parse($request->check_out_date);
        $durasiMenginap = $checkIn->diffInDays($checkOut);
        if ($durasiMenginap == 0) {
            $durasiMenginap = 1;
        }

        $hargaPerMalam = $kamar->tipeKamar->harga_per_malam;
        $totalHarga = $hargaPerMalam * $durasiMenginap;

        // Hitung biaya tambahan fasilitas
        if ($request->has('fasilitas_ids') && is_array($request->fasilitas_ids)) {
            $fasilitasDipilih = Fasilitas::whereIn('id_fasilitas', $request->fasilitas_ids)->get();
            foreach ($fasilitasDipilih as $fasilitas) {
                $totalHarga += $fasilitas->biaya_tambahan;
            }
        }

        $pemesanan = Pemesanan::create([
            'user_id' => Auth::id(),
            'kamar_id' => $kamar->id_kamar,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'jumlah_tamu' => $request->jumlah_tamu,
            'total_harga' => $totalHarga,
            'status_pemesanan' => 'pending',
        ]);

        if ($request->has('fasilitas_ids') && is_array($request->fasilitas_ids)) {
            $pemesanan->fasilitas()->attach($request->fasilitas_ids);
        }

        return redirect()->route('dashboard')->with('success', 'Pemesanan kamar berhasil dibuat! Total harga: Rp ' . number_format($totalHarga, 2, ',', '.'));
    }
}
