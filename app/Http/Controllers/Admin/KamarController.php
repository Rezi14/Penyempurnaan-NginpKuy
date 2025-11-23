<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kamar; // Import model Kamar
use App\Models\TipeKamar; // Import model TipeKamar (jika diperlukan untuk dropdown saat tambah/edit)
use Illuminate\Http\Request;

class KamarController extends Controller
{
    /**
     * Menampilkan daftar semua kamar.
     */
    public function index()
    {
        // Ambil semua kamar dengan memuat relasi tipeKamar
        $kamars = Kamar::with('tipeKamar')->orderBy('nomor_kamar')->get();
        
        return view('admin.kamars.index', compact('kamars'));
    }

    /**
     * Menampilkan formulir untuk membuat kamar baru.
     */
    public function create()
    {
        $tipeKamars = TipeKamar::all(); // Ambil semua tipe kamar untuk dropdown
        return view('admin.kamars.create', compact('tipeKamars'));
    }

    /**
     * Menyimpan kamar baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomor_kamar' => 'required|string|max:255|unique:kamars',
            'tipe_kamar_id' => 'required|exists:tipe_kamars,id_tipe_kamar',
            'status_kamar' => 'required|boolean', // 0 untuk Tidak Tersedia, 1 untuk Tersedia
        ]);

        Kamar::create($request->all());

        return redirect()->route('admin.kamars.index')->with('success', 'Kamar berhasil ditambahkan!');
    }

    /**
     * Menampilkan formulir untuk mengedit kamar.
     */
    public function edit(Kamar $kamar)
    {
        $tipeKamars = TipeKamar::all();
        return view('admin.kamars.edit', compact('kamar', 'tipeKamars'));
    }

    /**
     * Memperbarui kamar di database.
     */
    public function update(Request $request, Kamar $kamar)
    {
        $request->validate([
            'nomor_kamar' => 'required|string|max:255|unique:kamars,nomor_kamar,' . $kamar->id_kamar . ',id_kamar',
            'tipe_kamar_id' => 'required|exists:tipe_kamars,id_tipe_kamar',
            'status_kamar' => 'required|boolean',
        ]);

        $kamar->update($request->all());

        return redirect()->route('admin.kamars.index')->with('success', 'Kamar berhasil diperbarui!');
    }

    /**
     * Menghapus kamar dari database.
     */
    public function destroy(Kamar $kamar)
    {
        $kamar->delete();
        return redirect()->route('admin.kamars.index')->with('success', 'Kamar berhasil dihapus!');
    }
}