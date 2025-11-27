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
        $users = User::where('id_role', '!=', 1)->get(); // Opsional: Ambil user selain admin
        $kamars = Kamar::with('tipeKamar')
            ->where('status_kamar', 1) // Hanya tampilkan kamar yang tersedia (true/1)
            ->orderBy('nomor_kamar', 'asc')
            ->get();
        $fasilitas = Fasilitas::where('biaya_tambahan', '>', 0)->get();

        return view('admin.pemesanans.create', compact('users', 'kamars', 'fasilitas'));
    }

    /**
     * Menyimpan pemesanan baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $rules = [
            'kamar_id' => 'required|exists:kamars,id_kamar',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'jumlah_tamu' => 'required|integer|min:1',
            'total_harga' => 'required|numeric|min:0',
            'status_pemesanan' => 'required|string|in:pending,confirmed,checked_in,checked_out,cancelled,paid',
            'fasilitas_tambahan' => 'nullable|array',
            'fasilitas_tambahan.*' => 'exists:fasilitas,id_fasilitas',
        ];

        // Validasi khusus tipe pelanggan
        if ($request->input('customer_type') === 'new') {
            $rules['new_user_name'] = 'required|string|max:255';
            $rules['new_user_email'] = 'required|string|email|max:255|unique:users,email';
        } else {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        try {
            // 2. Tentukan User ID (Buat baru atau pakai yang ada)
            $userId = null;
            if ($request->input('customer_type') === 'new') {
                // Cari Role Customer (sesuaikan nama kolom di DB Anda, biasanya 'nama_role' atau 'name')
                // Asumsi berdasarkan User.php: kolomnya 'nama_role' dan PK 'id_role'
                $customerRole = Role::where('nama_role', 'customer')->first();
                $roleId = $customerRole ? $customerRole->id_role : 2; // Default ke 2 jika tidak ketemu

                $newUser = User::create([
                    'name' => $request->input('new_user_name'),
                    'email' => $request->input('new_user_email'),
                    'password' => Hash::make('password123'), // Password default
                    'id_role' => $roleId, // PERBAIKAN: Gunakan 'id_role', bukan 'role_id'
                ]);
                $userId = $newUser->id;
            } else {
                $userId = $request->input('user_id');
            }

            // 3. Simpan Pemesanan
            $pemesanan = Pemesanan::create([
                'user_id' => $userId,
                'kamar_id' => $request->input('kamar_id'),
                'check_in_date' => $request->input('check_in_date'),
                'check_out_date' => $request->input('check_out_date'),
                'jumlah_tamu' => $request->input('jumlah_tamu'),
                'total_harga' => $request->input('total_harga'),
                'status_pemesanan' => $request->input('status_pemesanan'),
            ]);

            // 4. Simpan Fasilitas Tambahan (Pivot Table)
            if ($request->has('fasilitas_tambahan')) {
                // Attach fasilitas ke pemesanan
                $pemesanan->fasilitas()->attach($request->input('fasilitas_tambahan'));
            }

            // 5. Update Status Kamar (Opsional: Jika ingin langsung mengubah status kamar jadi terisi)
            // $kamar = Kamar::find($request->input('kamar_id'));
            // $kamar->update(['status_kamar' => 0]);

            return redirect()->route('admin.pemesanans.index')->with('success', 'Pemesanan berhasil ditambahkan!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
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
        $fasilitas = Fasilitas::where('biaya_tambahan', '>', 0)->get();
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
            'jumlah_tamu' => 'required|integer|min:1',
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
                'jumlah_tamu' => $request->input('jumlah_tamu'),
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
     * Mengubah status pemesanan menjadi 'paid' (Selesai) tanpa perhitungan pembayaran ulang.
     */
    public function checkout(Pemesanan $pemesanan)
    {
        try {
            if ($pemesanan->status_pemesanan === 'checked_in') {
                // UPDATE: Langsung ubah status menjadi 'paid' karena pembayaran sudah di muka.
                // Status 'paid' akan memindahkan data ini ke riwayat transaksi.
                $pemesanan->status_pemesanan = 'paid';

                // Set waktu checkout jika belum ada
                if (is_null($pemesanan->check_out_date)) {
                    $pemesanan->check_out_date = Carbon::carbon();
                }

                $pemesanan->save();

                // Pastikan kamar tersedia kembali untuk dipesan
                $kamar = $pemesanan->kamar;
                if ($kamar) {
                    $kamar->status_kamar = 1; // Tersedia
                    $kamar->save();
                }

                // Redirect kembali ke daftar pemesanan (item ini akan hilang dari list aktif)
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Check out berhasil. Transaksi selesai (Pembayaran Lunas).');
            } else {
                return redirect()->back()->with('error', 'Pemesanan tidak dapat di-check out karena statusnya bukan "Checked In".');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat melakukan check out: ' . $e->getMessage());
        }
    }
    public function riwayat()
    {
        $riwayatPemesanan = Pemesanan::with(['user', 'kamar.tipeKamar'])
            ->whereIn('status_pemesanan', ['paid', 'cancelled'])
            ->orderBy('updated_at', 'desc') // Urutkan berdasarkan waktu transaksi terakhir
            ->get();

        return view('admin.riwayat.pemesanan', compact('riwayatPemesanan'));
    }

    public function detailRiwayat($id)
    {
        $pemesanan = Pemesanan::with(['user', 'kamar.tipeKamar', 'fasilitas'])->findOrFail($id);

        // Pastikan hanya bisa akses yang statusnya sudah selesai
        if (!in_array($pemesanan->status_pemesanan, ['paid', 'cancelled'])) {
            return redirect()->route('admin.pemesanans.index')
                ->with('error', 'Pesanan ini masih aktif, bukan riwayat.');
        }

        return view('admin.riwayat.detail', compact('pemesanan'));
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
