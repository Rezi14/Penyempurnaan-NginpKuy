<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FasilitasController extends Controller
{
    /**
     * Menampilkan daftar semua fasilitas.
     */
    public function index()
    {
        $fasilitas = Fasilitas::orderBy('id_fasilitas')->get();
        return view('admin.fasilitas.index', compact('fasilitas'));
    }

    /**
     * Menampilkan formulir untuk membuat fasilitas baru.
     */
    public function create()
    {
        return view('admin.fasilitas.create');
    }

    /**
     * Menyimpan fasilitas baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_fasilitas' => ['required', 'string', 'max:255', Rule::unique('fasilitas', 'nama_fasilitas')],
            'deskripsi' => 'nullable|string',
            'biaya_tambahan' => 'nullable|numeric|min:0',
            'icon' => 'nullable|string|max:255',
        ]);

        try {
            Fasilitas::create($request->all());
            return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan fasilitas: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail fasilitas tertentu (opsional).
     */
    public function show(Fasilitas $fasilitas)
    {
        return view('admin.fasilitas.show', compact('fasilitas'));
    }

    /**
     * Menampilkan formulir untuk mengedit fasilitas yang sudah ada.
     */
    public function edit($id) // Ubah parameter menjadi $id
    {
        $fasilitas = Fasilitas::findOrFail($id);
        return view('admin.fasilitas.edit', compact('fasilitas'));
    }


    /**
     * Memperbarui fasilitas di database.
     */
    public function update(Request $request, $id) // Ubah parameter menjadi $id
    {
        $fasilitas = Fasilitas::findOrFail($id);

        $request->validate([
            'nama_fasilitas' => ['required', 'string', 'max:255', Rule::unique('fasilitas')->ignore($fasilitas->id_fasilitas, 'id_fasilitas')],
            'deskripsi' => 'nullable|string',
            'biaya_tambahan' => 'nullable|numeric|min:0',
            'icon' => 'nullable|string|max:255',
        ]);

        try {
            $fasilitas->update($request->all());
            return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui fasilitas: ' . $e->getMessage())->withInput();
        }
    }

/**
 * Menghapus fasilitas dari database.
 */
    public function destroy($id) // Ubah parameter menjadi $id
    {
        $fasilitas = Fasilitas::findOrFail($id);

        try {
            $fasilitas->delete();
            return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus fasilitas: ' . $e->getMessage());
        }
    }
}
