<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Pemesanan;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PemesananController extends Controller
{
    /**
     * Menampilkan daftar semua pemesanan aktif (selain paid/cancelled).
     */
    public function index(): View
    {
        $pemesanans = Pemesanan::with(['user', 'kamar.tipeKamar', 'fasilitas'])
            ->whereNotIn('status_pemesanan', ['paid', 'cancelled'])
            ->orderBy('check_in_date', 'desc')
            ->get();

        return view('admin.pemesanans.index', compact('pemesanans'));
    }

    /**
     * Menampilkan formulir untuk membuat pemesanan baru.
     */
    public function create(): View
    {
        $users = User::where('id_role', '!=', 1)->get(); // Opsional: Ambil user selain admin

        $kamars = Kamar::with('tipeKamar')
            ->where('status_kamar', 1) // Hanya tampilkan kamar yang tersedia
            ->orderBy('nomor_kamar', 'asc')
            ->get();

        $fasilitas = Fasilitas::where('biaya_tambahan', '>', 0)->get();

        return view('admin.pemesanans.create', compact('users', 'kamars', 'fasilitas'));
    }

    /**
     * Menyimpan pemesanan baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Definisi Rules Dasar
        $rules = [
            'kamar_id'             => 'required|exists:kamars,id_kamar',
            'check_in_date'        => 'required|date|after_or_equal:today',
            'check_out_date'       => 'required|date|after:check_in_date',
            'jumlah_tamu'          => 'required|integer|min:1',
            'total_harga'          => 'required|numeric|min:0',
            'status_pemesanan'     => 'required|string|in:pending,confirmed,checked_in,checked_out,cancelled,paid',
            'fasilitas_tambahan'   => 'nullable|array',
            'fasilitas_tambahan.*' => 'exists:fasilitas,id_fasilitas',
            'customer_type'        => 'required|string|in:existing,new',
        ];

        // Validasi kondisional berdasarkan tipe pelanggan
        if ($request->input('customer_type') === 'new') {
            $rules['new_user_name']  = 'required|string|max:255';
            $rules['new_user_email'] = 'required|string|email|max:255|unique:users,email';
        } else {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            // 2. Tentukan User ID (Buat baru atau pakai yang ada)
            $userId = null;

            if ($request->input('customer_type') === 'new') {
                $customerRole = Role::where('nama_role', 'customer')->first();
                $roleId = $customerRole ? $customerRole->id_role : 2; // Default ke 2 jika tidak ketemu

                $newUser = User::create([
                    'name'     => $request->input('new_user_name'),
                    'email'    => $request->input('new_user_email'),
                    'password' => Hash::make('password123'), // Password default
                    'id_role'  => $roleId,
                ]);
                $userId = $newUser->id;
            } else {
                $userId = $request->input('user_id');
            }

            // 3. Simpan Pemesanan
            $pemesanan = Pemesanan::create([
                'user_id'          => $userId,
                'kamar_id'         => $request->input('kamar_id'),
                'check_in_date'    => $request->input('check_in_date'),
                'check_out_date'   => $request->input('check_out_date'),
                'jumlah_tamu'      => $request->input('jumlah_tamu'),
                'total_harga'      => $request->input('total_harga'),
                'status_pemesanan' => $request->input('status_pemesanan'),
            ]);

            // 4. Simpan Fasilitas Tambahan (Pivot Table)
            if ($request->has('fasilitas_tambahan')) {
                $pemesanan->fasilitas()->attach($request->input('fasilitas_tambahan'));
            }

            // 5. Update Status Kamar (Menjadi tidak tersedia)
            $kamar = Kamar::find($request->input('kamar_id'));
            $kamar->update(['status_kamar' => 0]);

            return redirect()->route('admin.pemesanans.index')
                ->with('success', 'Pemesanan berhasil ditambahkan!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail pemesanan tertentu.
     */
    public function show(Pemesanan $pemesanan): View
    {
        $pemesanan->load(['user', 'kamar.tipeKamar', 'fasilitas']);

        return view('admin.pemesanans.show', compact('pemesanan'));
    }

    /**
     * Menampilkan formulir untuk mengedit pemesanan.
     */
    public function edit(Pemesanan $pemesanan): View
    {
        $users  = User::all();
        $kamars = Kamar::all();
        // Koreksi: menghapus duplikasi query Fasilitas::all()
        $fasilitas = Fasilitas::where('biaya_tambahan', '>', 0)->get();
        $selectedFasilitas = $pemesanan->fasilitas->pluck('id_fasilitas')->toArray();

        return view('admin.pemesanans.edit', compact('pemesanan', 'users', 'kamars', 'fasilitas', 'selectedFasilitas'));
    }

    /**
     * Memperbarui pemesanan di database.
     */
    public function update(Request $request, Pemesanan $pemesanan): RedirectResponse
    {
        $rules = [
            'kamar_id'             => 'required|exists:kamars,id_kamar',
            'check_in_date'        => 'required|date',
            'check_out_date'       => 'required|date|after:check_in_date',
            'jumlah_tamu'          => 'required|integer|min:1',
            'status_pemesanan'     => 'required|string|in:pending,confirmed,checked_in,checked_out,cancelled,paid',
            'fasilitas_tambahan'   => 'nullable|array',
            'fasilitas_tambahan.*' => 'exists:fasilitas,id_fasilitas',
        ];

        $request->validate($rules);

        try {
            // Hitung ulang total harga
            $selectedFasilitasIds = $request->input('fasilitas_tambahan', []);
            $fasilitasTambahanObjects = Fasilitas::whereIn('id_fasilitas', $selectedFasilitasIds)->get();
            $biayaTambahanTotal = $fasilitasTambahanObjects->sum('biaya_tambahan');

            $kamar = Kamar::findOrFail($request->input('kamar_id'));
            $hargaPerMalam = $kamar->tipeKamar->harga_per_malam;

            $checkInDate  = Carbon::parse($request->input('check_in_date'));
            $checkOutDate = Carbon::parse($request->input('check_out_date'));
            $diffDays     = $checkInDate->diffInDays($checkOutDate);
            // Minimal 1 hari jika check-in dan check-out di hari yang sama (opsional, tergantung kebijakan)
            if ($diffDays == 0) $diffDays = 1;

            $hargaKamarTotal = $hargaPerMalam * $diffDays;
            $finalTotalHarga = $hargaKamarTotal + $biayaTambahanTotal;

            $pemesanan->update([
                'kamar_id'         => $request->input('kamar_id'),
                'check_in_date'    => $request->input('check_in_date'),
                'check_out_date'   => $request->input('check_out_date'),
                'jumlah_tamu'      => $request->input('jumlah_tamu'),
                'total_harga'      => $finalTotalHarga,
                'status_pemesanan' => $request->input('status_pemesanan'),
            ]);

            $pemesanan->fasilitas()->sync($selectedFasilitasIds);

            return redirect()->route('admin.pemesanans.index')
                ->with('success', 'Pemesanan berhasil diperbarui!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui pemesanan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Mengubah status pemesanan menjadi 'confirmed'.
     */
    public function confirm(Pemesanan $pemesanan): RedirectResponse
    {
        try {
            if ($pemesanan->status_pemesanan === 'pending') {
                $pemesanan->status_pemesanan = 'confirmed';
                $pemesanan->save();

                return redirect()->back()->with('success', 'Pemesanan berhasil dikonfirmasi!');
            }

            return redirect()->back()->with('error', 'Pemesanan tidak dapat dikonfirmasi karena statusnya bukan "Pending".');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Mengubah status pemesanan menjadi 'checked_in'.
     */
    public function checkIn(Pemesanan $pemesanan): RedirectResponse
    {
        try {
            if ($pemesanan->status_pemesanan === 'confirmed') {
                $pemesanan->status_pemesanan = 'checked_in';
                $pemesanan->save();

                return redirect()->back()->with('success', 'Pemesanan berhasil di-check in!');
            }

            return redirect()->back()->with('error', 'Pemesanan tidak dapat di-check in karena statusnya bukan "Confirmed".');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Mengubah status pemesanan menjadi 'paid' (Selesai).
     */
    public function checkout(Pemesanan $pemesanan): RedirectResponse
    {
        try {
            if ($pemesanan->status_pemesanan === 'checked_in') {
                $pemesanan->status_pemesanan = 'paid';
                // Opsional: Set check_out_date real-time di sini jika diperlukan
                $pemesanan->save();

                // Kembalikan status kamar menjadi tersedia
                $kamar = $pemesanan->kamar;
                if ($kamar) {
                    $kamar->status_kamar = 1; // Tersedia
                    $kamar->save();
                }

                return redirect()->route('admin.dashboard')
                    ->with('success', 'Check out berhasil. Transaksi selesai (Pembayaran Lunas).');
            }

            return redirect()->back()->with('error', 'Pemesanan tidak dapat di-check out karena statusnya bukan "Checked In".');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat check out: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan riwayat pemesanan (paid/cancelled).
     */
    public function riwayat(): View
    {
        $riwayatPemesanan = Pemesanan::with(['user', 'kamar.tipeKamar'])
            ->whereIn('status_pemesanan', ['paid', 'cancelled'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.riwayat.pemesanan', compact('riwayatPemesanan'));
    }

    /**
     * Menampilkan detail riwayat pemesanan.
     */
    public function detailRiwayat($id)
    {
        $pemesanan = Pemesanan::with(['user', 'kamar.tipeKamar', 'fasilitas'])->findOrFail($id);

        if (!in_array($pemesanan->status_pemesanan, ['paid', 'cancelled'])) {
            return redirect()->route('admin.pemesanans.index')
                ->with('error', 'Pesanan ini masih aktif, bukan riwayat.');
        }

        return view('admin.riwayat.detail', compact('pemesanan'));
    }

    /**
     * Menghapus pemesanan dari database.
     */
    public function destroy(Pemesanan $pemesanan): RedirectResponse
    {
        try {
            // 1. Validasi: Jangan hapus data transaksi lunas
            if ($pemesanan->status_pemesanan === 'paid') {
                return redirect()->back()
                    ->with('error', 'ILLEGAL ACTION: Transaksi yang sudah lunas (Paid) tidak boleh dihapus.');
            }

            // 2. Validasi: Hapus Confirmed/Checked In hanya jika lewat tanggal checkout
            if (in_array($pemesanan->status_pemesanan, ['confirmed', 'checked_in'])) {
                $checkOutDate = Carbon::parse($pemesanan->check_out_date)->endOfDay();

                if (Carbon::now()->lessThan($checkOutDate)) {
                    return redirect()->back()
                        ->with('error', 'Gagal Hapus: Pemesanan aktif hanya dapat dihapus setelah melewati tanggal checkout.');
                }
            }

            // 3. Kembalikan Status Kamar Jadi Tersedia
            $kamar = $pemesanan->kamar;
            if ($kamar) {
                $kamar->status_kamar = 1; // Tersedia
                $kamar->save();
            }

            // 4. Hapus Data
            $pemesanan->fasilitas()->detach();
            $pemesanan->delete();

            return redirect()->route('admin.pemesanans.index')
                ->with('success', 'Pemesanan berhasil dihapus dan kamar kini tersedia kembali!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus pemesanan: ' . $e->getMessage());
        }
    }
}
