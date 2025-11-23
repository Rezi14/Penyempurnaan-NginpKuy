<?php

use Illuminate\Support\Facades\Route;

// --- Import Semua Controller di Atas ---

// Controller User
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\BookingController;

// Controller Autentikasi
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Controller Admin
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\KamarController;
use App\Http\Controllers\Admin\TipeKamarController;
use App\Http\Controllers\Admin\PemesananController;
use App\Http\Controllers\Admin\PembayaranController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FasilitasController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sinilah Anda dapat mendaftarkan rute web untuk aplikasi Anda.
|
*/

// --- Rute Umum (BISA DIAKSES TANPA LOGIN) ---

// Jadikan URL / (root) me-redirect ke halaman dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Jadikan /dashboard sebagai rute utama dengan nama 'dashboard'
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// --- Rute Autentikasi (Login, Register, Logout) ---
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});


// --- Grup Rute yang MEMERLUKAN AUTENTIKASI (Login) ---
Route::middleware('auth')->group(function () {

    // Rute Pemesanan Kamar (User Biasa)
    Route::controller(BookingController::class)->group(function () {
        Route::get('/pesan-kamar/{kamar}', 'showBookingForm')->name('booking.create');
        Route::post('/pesan-kamar', 'store')->name('booking.store');
    });

    // --- Grup Rute Khusus ADMIN ---
    // Middleware 'auth' tidak perlu ditulis lagi karena sudah dicakup oleh grup luar
    Route::middleware('role')->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard-admin', [DashboardAdminController::class, 'index'])->name('dashboard');

        // CRUD Resources
        Route::resource('kamars', KamarController::class);
        Route::resource('tipe_kamars', TipeKamarController::class);
        Route::resource('users', UserController::class);
        Route::resource('fasilitas', FasilitasController::class);

        // Grup Rute Pemesanan (Resource + Aksi Tambahan)
        Route::resource('pemesanans', PemesananController::class);
        Route::controller(PemesananController::class)->prefix('pemesanans/{pemesanan}')->name('pemesanans.')->group(function () {
            Route::patch('/checkin', 'checkIn')->name('checkin');
            Route::patch('/checkout', 'checkout')->name('checkout');
            Route::patch('/confirm', 'confirm')->name('confirm');
        });

        // Grup Rute Pembayaran
        Route::controller(PembayaranController::class)->group(function () {
            Route::get('pembayaran/{pemesanan}', 'show')->name('pembayaran.show');
            Route::post('pembayaran/{pemesanan}/process', 'process')->name('pembayaran.process');
            Route::get('riwayat-transaksi', 'history')->name('riwayat.transaksi');
        });

        // Rute fasilitas yang di-comment sebelumnya
        // Route::put('fasilitas/{fasilitas}', [FasilitasController::class, 'update'])->name('fasilitas.update');
        // Note: Route::resource('fasilitas', ...) sudah mencakup update, jadi baris ini mungkin tidak perlu
    });
});
