<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipeKamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; // Tambahkan ini untuk manajemen file

class TipeKamarController extends Controller
{
    public function index()
    {
        $tipeKamars = TipeKamar::orderBy('id_tipe_kamar')->get();
        return view('admin.tipe_kamars.index', compact('tipeKamars'));
    }

    public function create()
    {
        return view('admin.tipe_kamars.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_tipe_kamar' => 'required|string|max:255|unique:tipe_kamars',
            'harga_per_malam' => 'required|numeric|min:0',
            'kapasitas' => 'required|integer|min:1', // <--- Validasi Baru
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();

        // Logika Upload Gambar
        if ($request->hasFile('foto')) {
            $image = $request->file('foto');
            $imageName = time() . '_' . $image->getClientOriginalName(); // Nama file unik
            $image->move(public_path('img'), $imageName); // Simpan ke folder public/img
            $data['foto_url'] = '/img/' . $imageName; // Simpan path ke database
        }

        TipeKamar::create($data);

        return redirect()->route('admin.tipe_kamars.index')->with('success', 'Tipe kamar berhasil ditambahkan!');
    }

    public function edit(TipeKamar $tipeKamar)
    {
        return view('admin.tipe_kamars.edit', compact('tipeKamar'));
    }

    public function update(Request $request, TipeKamar $tipeKamar)
    {
        $request->validate([
            'nama_tipe_kamar' => 'required|string|max:255|unique:tipe_kamars,nama_tipe_kamar,' . $tipeKamar->id_tipe_kamar . ',id_tipe_kamar',
            'harga_per_malam' => 'required|numeric|min:0',
            'kapasitas' => 'required|integer|min:1', // <--- Validasi Baru
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();

        // Logika Upload Gambar (Update)
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada dan bukan foto default (opsional)
            if ($tipeKamar->foto_url && file_exists(public_path($tipeKamar->foto_url))) {
                // File::delete(public_path($tipeKamar->foto_url)); // Uncomment jika ingin menghapus foto lama
            }

            $image = $request->file('foto');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('img'), $imageName);
            $data['foto_url'] = '/img/' . $imageName;
        }

        $tipeKamar->update($data);

        return redirect()->route('admin.tipe_kamars.index')->with('success', 'Tipe kamar berhasil diperbarui!');
    }

    public function destroy(TipeKamar $tipeKamar)
    {
        if ($tipeKamar->kamars()->count() > 0) {
            return redirect()->route('admin.tipe_kamars.index')->with('error', 'Tidak dapat menghapus tipe kamar karena masih ada kamar yang terkait.');
        }

        // Hapus file foto jika ada
        if ($tipeKamar->foto_url && file_exists(public_path($tipeKamar->foto_url))) {
            File::delete(public_path($tipeKamar->foto_url));
        }

        $tipeKamar->delete();
        return redirect()->route('admin.tipe_kamars.index')->with('success', 'Tipe kamar berhasil dihapus!');
    }
}
