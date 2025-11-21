<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipeKamar; // Import model TipeKamar
use Illuminate\Http\Request;

class TipeKamarController extends Controller
{
    /**
     * Menampilkan daftar semua tipe kamar.
     */
    public function index()
    {
        $tipeKamars = TipeKamar::orderBy('id_tipe_kamar')->get();
        return view('admin.tipe_kamars.index', compact('tipeKamars'));
    }

    /**
     * Menampilkan formulir untuk membuat tipe kamar baru.
     */
    public function create()
    {
        return view('admin.tipe_kamars.create');
    }

    /**
     * Menyimpan tipe kamar baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_tipe_kamar' => 'required|string|max:255|unique:tipe_kamars',
            'harga_per_malam' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'foto_url' => 'nullable|string', // Pastikan format valid, atau gunakan upload file jika itu yang Anda inginkan
        ]);

        TipeKamar::create($request->all());

        return redirect()->route('admin.tipe_kamars.index')->with('success', 'Tipe kamar berhasil ditambahkan!');
    }

    /**
     * Menampilkan formulir untuk mengedit tipe kamar.
     */
    public function edit(TipeKamar $tipeKamar)
    {
        return view('admin.tipe_kamars.edit', compact('tipeKamar'));
    }

    /**
     * Memperbarui tipe kamar di database.
     */
    public function update(Request $request, TipeKamar $tipeKamar)
    {
        $request->validate([
            'nama_tipe_kamar' => 'required|string|max:255|unique:tipe_kamars,nama_tipe_kamar,' . $tipeKamar->id_tipe_kamar . ',id_tipe_kamar',
            'harga_per_malam' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'foto_url' => 'nullable|string',
        ]);

        $tipeKamar->update($request->all());

        return redirect()->route('admin.tipe_kamars.index')->with('success', 'Tipe kamar berhasil diperbarui!');
    }

    /**
     * Menghapus tipe kamar dari database.
     */
    public function destroy(TipeKamar $tipeKamar)
    {
        // Pertimbangkan logika untuk tidak menghapus tipe kamar jika ada kamar yang terkait
        if ($tipeKamar->kamars()->count() > 0) {
            return redirect()->route('admin.tipe_kamars.index')->with('error', 'Tidak dapat menghapus tipe kamar karena masih ada kamar yang terkait.');
        }

        $tipeKamar->delete();
        return redirect()->route('admin.tipe_kamars.index')->with('success', 'Tipe kamar berhasil dihapus!');
    }
}