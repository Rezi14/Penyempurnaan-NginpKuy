<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipeKamar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TipeKamarController extends Controller
{
    /**
     * Menampilkan daftar tipe kamar.
     */
    public function index(): View
    {
        $tipeKamars = TipeKamar::orderBy('id_tipe_kamar', 'asc')->get();

        return view('admin.tipe_kamars.index', compact('tipeKamars'));
    }

    /**
     * Menampilkan form tambah tipe kamar.
     */
    public function create(): View
    {
        return view('admin.tipe_kamars.create');
    }

    /**
     * Menyimpan tipe kamar baru.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'nama_tipe_kamar' => ['required', 'string', 'max:255', Rule::unique('tipe_kamars', 'nama_tipe_kamar')],
            'harga_per_malam' => ['required', 'numeric', 'min:0'],
            'kapasitas'       => ['required', 'integer', 'min:1'],
            'deskripsi'       => ['nullable', 'string'],
            'foto'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        // 2. Logika Upload Gambar
        if ($request->hasFile('foto')) {
            $image = $request->file('foto');
            $imageName = time() . '_' . $image->getClientOriginalName(); // Nama file unik
            $image->move(public_path('img'), $imageName); // Simpan ke folder public/img

            // Simpan path ke array data yang akan disimpan ke DB
            $validatedData['foto_url'] = '/img/' . $imageName;
        }

        // Hapus key 'foto' karena kita menyimpan 'foto_url' di database
        unset($validatedData['foto']);

        // 3. Simpan ke Database
        TipeKamar::create($validatedData);

        return redirect()->route('admin.tipe_kamars.index')
            ->with('success', 'Tipe kamar berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit tipe kamar.
     */
    public function edit(TipeKamar $tipeKamar): View
    {
        return view('admin.tipe_kamars.edit', compact('tipeKamar'));
    }

    /**
     * Memperbarui tipe kamar.
     */
    public function update(Request $request, TipeKamar $tipeKamar): RedirectResponse
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'nama_tipe_kamar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tipe_kamars', 'nama_tipe_kamar')->ignore($tipeKamar->id_tipe_kamar, 'id_tipe_kamar')
            ],
            'harga_per_malam' => ['required', 'numeric', 'min:0'],
            'kapasitas'       => ['required', 'integer', 'min:1'],
            'deskripsi'       => ['nullable', 'string'],
            'foto'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        // 2. Logika Upload Gambar (Update)
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada dan file fisiknya eksis
            if ($tipeKamar->foto_url && File::exists(public_path($tipeKamar->foto_url))) {
                File::delete(public_path($tipeKamar->foto_url));
            }

            // Upload foto baru
            $image = $request->file('foto');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('img'), $imageName);

            $validatedData['foto_url'] = '/img/' . $imageName;
        }

        // Hapus key 'foto' dari array validated agar tidak error saat update (karena kolom 'foto' tidak ada di DB)
        unset($validatedData['foto']);

        // 3. Update Database
        $tipeKamar->update($validatedData);

        return redirect()->route('admin.tipe_kamars.index')
            ->with('success', 'Tipe kamar berhasil diperbarui!');
    }

    /**
     * Menghapus tipe kamar.
     */
    public function destroy(TipeKamar $tipeKamar): RedirectResponse
    {
        // 1. Cek Relasi: Apakah tipe kamar ini sedang digunakan oleh data kamar?
        if ($tipeKamar->kamars()->count() > 0) {
            return redirect()->route('admin.tipe_kamars.index')
                ->with('error', 'Tidak dapat menghapus tipe kamar karena masih ada kamar yang terkait.');
        }

        // 2. Hapus file foto fisik jika ada
        if ($tipeKamar->foto_url && File::exists(public_path($tipeKamar->foto_url))) {
            File::delete(public_path($tipeKamar->foto_url));
        }

        // 3. Hapus data dari database
        $tipeKamar->delete();

        return redirect()->route('admin.tipe_kamars.index')
            ->with('success', 'Tipe kamar berhasil dihapus!');
    }
}
