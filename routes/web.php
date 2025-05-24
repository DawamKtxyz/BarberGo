<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TukangCukurController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\LaporanPenggajianController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PendapatanController;
use App\Http\Controllers\PenggajianController;



// Rute Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/laporan_penggajian', [AuthController::class, 'laporan_penggajian'])->name('laporan_penggajian');
Route::post('/pesanan', [AuthController::class, 'pesanan'])->name('pesanan');

// Rute dengan middleware admin
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // Pelanggan routes
        Route::resource('pelanggan', PelangganController::class);

        // Tukang Cukur routes
        Route::resource('tukang_cukur', TukangCukurController::class);

        // Admin routes
        Route::resource('admin', AdminController::class);

        Route::resource('laporan_penggajian', LaporanPenggajianController::class);
        Route::resource('pesanan', PesananController::class);
        // Existing routes...
        Route::get('/pesanan/get-jadwal/{id}', [PesananController::class, 'getJadwal'])->name('pesanan.get-jadwal');

        // Tambahkan route baru
        Route::get('/pesanan/get-barber-details/{id}', [PesananController::class, 'getBarberDetails'])->name('pesanan.get-barber-details');
        Route::get('/pesanan/get-pelanggan-details/{id}', [PesananController::class, 'getPelangganDetails'])->name('pesanan.get-pelanggan-details');
        Route::get('/pesanan/get-jadwal/{id}', [PesananController::class, 'getJadwal']);

        // Routes untuk Pembayaran Midtrans
        Route::get('/pembayaran/{pesanan}', 'App\Http\Controllers\PembayaranController@show')->name('pembayaran.show');
        Route::post('/pembayaran/{pesanan}/process', 'App\Http\Controllers\PembayaranController@process')->name('pembayaran.process');

        // Endpoints untuk callback Midtrans
        Route::post('/pembayaran/notification', 'App\Http\Controllers\PembayaranController@notification')->name('pembayaran.notification');
        Route::get('/pembayaran/finish', 'App\Http\Controllers\PembayaranController@finish')->name('pembayaran.finish');
        Route::get('/pembayaran/unfinish', 'App\Http\Controllers\PembayaranController@unfinish')->name('pembayaran.unfinish');
        Route::get('/pembayaran/error', 'App\Http\Controllers\PembayaranController@error')->name('pembayaran.error');

        Route::prefix('penggajian')->name('penggajian.')->group(function () {
            Route::get('/', [PenggajianController::class, 'index'])->name('index');
            Route::post('/generate', [PenggajianController::class, 'generate'])->name('generate');
            Route::post('/bayar', [PenggajianController::class, 'bayar'])->name('bayar');
            Route::get('/{id}/edit', [PenggajianController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PenggajianController::class, 'update'])->name('update');
            Route::delete('/{id}', [PenggajianController::class, 'destroy'])->name('destroy');
});
});

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/laporan-penggajian/cetak', [LaporanPenggajianController::class, 'cetakPdf'])->name('laporan_penggajian.cetak');
Route::get('pendapatan', [PendapatanController::class, 'pendapatan'])->name('pendapatan');

