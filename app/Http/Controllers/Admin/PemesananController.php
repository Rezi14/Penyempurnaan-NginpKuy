<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use App\Models\User;
use App\Models\Role;
use App\Models\Kamar;
use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PemesananController extends Controller
{
    /**
     * Menampilkan daftar semua pemesanan.
     */
    public function index()
    {
        // Hanya tampilkan pemesanan yang statusnya bukan 'paid' atau 'cancelled' di dashboard utama
        $pemesanans = Pemesanan::with(['user', 'kamar.tipeKamar', 'fasilitas'])
                               ->whereNotIn('status_pemesanan', ['paid', 'cancelled'])
                               ->orderBy('check_in_date', 'desc')
                               ->get();

        return view('admin.pemesanans.index', compact('pemesanans'));
    }

    /**
     * Menampilkan formulir untuk membuat pemesanan baru.
     */
    public function create()
    {
        $users = User::all();
        $kamars = Kamar::with('tipeKamar')
                ->orderBy('nomor_kamar', 'asc') // urut berdasarkan id_kamar (naik)
                ->get();
        $fasilitas = Fasilitas::all();

        return view('admin.pemesanans.create', compact('users', 'kamars', 'fasilitas'));
    }

    /**
     * Menyimpan pemesanan baru ke database.
     */
    public function store(Request $request)
    {
        $rules = [
            'kamar_id' => 'required|exists:kamars,id_kamar',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'jumlah_tamu' => 'required|integer|min:1', // Perbaikan di sini
            'total_harga' => 'required|numeric|min:0',
            'status_pemesanan' => 'required|string|in:pending,confirmed,checked_in,checked_out,cancelled,paid',
            'fasilitas_tambahan' => 'nullable|array',
            'fasilitas_tambahan.*' => 'exists:fasilitas,id_fasilitas',
        ];

        if ($request->input('customer_type') === 'new') {
            $rules['new_user_name'] = 'required|string|max:255';
            $rules['new_user_email'] = 'required|string|email|max:255|unique:users,email';
        } else {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            $userId = null;
            if ($request->input('customer_type') === 'new') {
                $newUser = User::create([
                    'name' => $request->input('new_user_name'),
                    'email' => $request->input('new_user_email'),
                    'password' => Hash::make(Str::random(10)),
                    'role_id' => Role::where('name', 'customer')->first()->id ?? 2,
                ]);
                $userId = $newUser->id;
            } else {
                $userId = $request->input('user_id');
            }

            $pemesanan = Pemesanan::create([
                'user_id' => $userId,
                'kamar_id' => $request->input('kamar_id'),
                'check_in_date' => $request->input('check_in_date'),
                'check_out_date' => $request->input('check_out_date'),
                'jumlah_tamu' => $request->input('jumlah_tamu'), // Perbaikan di sini
                'total_harga' => $request->input('total_harga'),
                'status_pemesanan' => $request->input('status_pemesanan'),
            ]);

            if ($request->has('fasilitas_tambahan')) {
                $pemesanan->fasilitas()->attach($request->input('fasilitas_tambahan'));
            }

            return redirect()->route('admin.pemesanans.index')->with('success', 'Pemesanan dan pelanggan berhasil ditambahkan!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan pemesanan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail pemesanan tertentu.
     */
    public function show(Pemesanan $pemesanan)
    {
        $pemesanan->load(['user', 'kamar.tipeKamar', 'fasilitas']);
        return view('admin.pemesanans.show', compact('pemesanan'));
    }

    /**
     * Menampilkan formulir untuk mengedit pemesanan yang sudah ada.
     */
    public function edit(Pemesanan $pemesanan)
    {
        $users = User::all();
        $kamars = Kamar::all();
        $fasilitas = Fasilitas::all();
        $selectedFasilitas = $pemesanan->fasilitas->pluck('id_fasilitas')->toArray();

        return view('admin.pemesanans.edit', compact('pemesanan', 'users', 'kamars', 'fasilitas', 'selectedFasilitas'));
    }

    /**
     * Memperbarui pemesanan di database.
     */
    public function update(Request $request, Pemesanan $pemesanan)
    {
        $rules = [
            'kamar_id' => 'required|exists:kamars,id_kamar',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'jumlah_tamu' => 'required|integer|min:1', // Tambahkan ini
            'total_harga' => 'required|numeric|min:0',
            'status_pemesanan' => 'required|string|in:pending,confirmed,checked_in,checked_out,cancelled,paid',
            'fasilitas_tambahan' => 'nullable|array',
            'fasilitas_tambahan.*' => 'exists:fasilitas,id_fasilitas',
        ];

        $request->validate($rules);

        try {
            $selectedFasilitasIds = $request->input('fasilitas_tambahan', []);
            $fasilitasTambahanObjects = Fasilitas::whereIn('id_fasilitas', $selectedFasilitasIds)->get();
            $biayaTambahanTotal = $fasilitasTambahanObjects->sum('biaya_tambahan');

            $kamar = Kamar::findOrFail($request->input('kamar_id'));
            $hargaPerMalam = $kamar->tipeKamar->harga_per_malam;

            $checkInDate = Carbon::parse($request->input('check_in_date'));
            $checkOutDate = Carbon::parse($request->input('check_out_date'));
            $diffDays = $checkInDate->diffInDays($checkOutDate);

            $hargaKamarTotal = $hargaPerMalam * $diffDays;
            $finalTotalHarga = $hargaKamarTotal + $biayaTambahanTotal;

            $pemesanan->update([
                'kamar_id' => $request->input('kamar_id'),
                'check_in_date' => $request->input('check_in_date'),
                'check_out_date' => $request->input('check_out_date'),
                'jumlah_tamu' => $request->input('jumlah_tamu'), // Tambahkan ini
                'total_harga' => $finalTotalHarga,
                'status_pemesanan' => $request->input('status_pemesanan'),
            ]);

            $pemesanan->fasilitas()->sync($selectedFasilitasIds);

            return redirect()->route('admin.pemesanans.index')->with('success', 'Pemesanan berhasil diperbarui!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui pemesanan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mengubah status pemesanan menjadi 'confirmed'.
     */
    public function confirm(Pemesanan $pemesanan)
    {
        try {
            if ($pemesanan->status_pemesanan === 'pending') {
                $pemesanan->status_pemesanan = 'confirmed';
                $pemesanan->save();
                return redirect()->back()->with('success', 'Pemesanan berhasil dikonfirmasi!');
            } else {
                return redirect()->back()->with('error', 'Pemesanan tidak dapat dikonfirmasi karena statusnya bukan "Pending".');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengkonfirmasi pemesanan: ' . $e->getMessage());
        }
    }


    public function checkIn(Pemesanan $pemesanan)
    {
        try {
            if ($pemesanan->status_pemesanan === 'confirmed') {
                $pemesanan->status_pemesanan = 'checked_in';
                $pemesanan->save();
                return redirect()->back()->with('success', 'Pemesanan berhasil di-check in!');
            } else {
                return redirect()->back()->with('error', 'Pemesanan tidak dapat di-check in karena statusnya bukan "Confirmed".');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat melakukan check in: ' . $e->getMessage());
        }
    }

    /**
     * Mengubah status pemesanan menjadi 'checked_out' dan MENGARAHKAN KE HALAMAN PEMBAYARAN.
     */
    public function checkout(Pemesanan $pemesanan)
    {
        try {
            if ($pemesanan->status_pemesanan === 'checked_in') {
                // Perbarui status pemesanan menjadi 'checked_out'
                $pemesanan->status_pemesanan = 'checked_out';
                if (is_null($pemesanan->check_out_date)) {
                    $pemesanan->check_out_date = Carbon::now();
                }
                $pemesanan->save();

                // Redirect ke halaman pembayaran, bawa ID pemesanan
                return redirect()->route('admin.pembayaran.show', $pemesanan->id_pemesanan)
                                ->with('success', 'Pemesanan berhasil di-check out. Lanjutkan ke pembayaran.');
            } else {
                return redirect()->back()->with('error', 'Pemesanan tidak dapat di-check out karena statusnya bukan "Checked In".');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat melakukan check out: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus pemesanan dari database.
     */
    public function destroy(Pemesanan $pemesanan)
    {
        try {
            $pemesanan->fasilitas()->detach();
            $pemesanan->delete();
            return redirect()->route('admin.pemesanans.index')->with('success', 'Pemesanan berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus pemesanan: ' . $e->getMessage());
        }
    }
}
