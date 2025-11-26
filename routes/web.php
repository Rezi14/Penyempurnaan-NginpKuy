<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;

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
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Rute Umum (BISA DIAKSES TANPA LOGIN) ---
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/kontak', function () {
    return view('user.pages.contact');
})->name('contact');

// --- Rute Autentikasi ---
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});

// --- Rute Forgot & Reset Password ---
Route::controller(ForgotPasswordController::class)->group(function () {
    Route::get('/forgot-password', 'showLinkRequestForm')->name('password.request');
    Route::post('/forgot-password', 'sendResetLinkEmail')->name('password.email');
});

Route::controller(ResetPasswordController::class)->group(function () {
    Route::get('/reset-password/{token}', 'showResetForm')->name('password.reset');
    Route::post('/reset-password', 'reset')->name('password.update');
});

// --- Rute Verifikasi Email ---
Route::middleware('auth')->group(function () {
    Route::controller(VerificationController::class)->group(function () {
        Route::get('/email/verify', 'show')->name('verification.notice');
        Route::get('/email/verify/{id}/{hash}', 'verify')->middleware(['signed'])->name('verification.verify');
        Route::post('/email/verification-notification', 'resend')->middleware(['throttle:6,1'])->name('verification.send');
    });
});

// --- Grup Rute yang MEMERLUKAN AUTENTIKASI ---
Route::middleware('auth')->group(function () {

    Route::get('/profile', function () {
        return view('user.pages.profile', ['user' => Illuminate\Support\Facades\Auth::user()]);
    })->name('profile');

    // Rute Pemesanan Kamar (User Biasa)
    Route::controller(BookingController::class)->middleware('verified')->group(function () {
        Route::get('/pesan-kamar/{kamar}', 'showBookingForm')->name('booking.create');
        Route::post('/pesan-kamar', 'store')->name('booking.store');

        Route::get('/pembayaran/{id}', 'showPayment')->name('booking.payment');
        Route::get('/pembayaran/{id}/check', 'checkPaymentStatus')->name('booking.payment.check');
        Route::post('/pembayaran/{id}/cancel', 'cancelBooking')->name('booking.payment.cancel');
        Route::get('/simulasi/qr-scan/{id}', 'simulatePaymentSuccess')->name('simulation.qr.scan');
    });

    // --- Grup Rute Khusus ADMIN ---
    Route::middleware(['auth', RoleMiddleware::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('dashboard');

        // CRUD Resources
        Route::resource('kamars', KamarController::class);
        Route::resource('tipe_kamars', TipeKamarController::class);
        Route::resource('users', UserController::class);
        Route::resource('fasilitas', FasilitasController::class);

        // Grup Rute Pemesanan
        Route::resource('pemesanans', PemesananController::class);
        Route::controller(PemesananController::class)->prefix('pemesanans/{pemesanan}')->name('pemesanans.')->group(function () {
            Route::patch('/checkin', 'checkIn')->name('checkin');
            Route::patch('/checkout', 'checkout')->name('checkout');
            Route::patch('/confirm', 'confirm')->name('confirm');
        });

        Route::controller(PemesananController::class)->group(function () {
            // Menggantikan riwayat-transaksi lama
            Route::get('riwayat/pemesanan', 'riwayat')->name('riwayat.pemesanan');
            Route::get('riwayat/pemesanan/{id}', 'detailRiwayat')->name('riwayat.detail');
        });
    });
});
