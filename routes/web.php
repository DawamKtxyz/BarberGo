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
use App\Http\Controllers\PembayaranController;
use App\Models\Pesanan;

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

        // Routes untuk verifikasi tukang cukur
        Route::put('tukang_cukur/{tukangCukur}/verify', [TukangCukurController::class, 'verify'])->name('tukang_cukur.verify');
        Route::put('tukang_cukur/{tukangCukur}/unverify', [TukangCukurController::class, 'unverify'])->name('tukang_cukur.unverify');

        // Admin routes
        Route::resource('admin', AdminController::class);

        // Route::resource('laporan_penggajian', LaporanPenggajianController::class);
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

         // Updated Penggajian routes
        Route::prefix('penggajian')->name('penggajian.')->group(function () {
        Route::get('/', [PenggajianController::class, 'index'])->name('index');
        Route::get('/create', [PenggajianController::class, 'create'])->name('create');
        Route::post('/generate', [PenggajianController::class, 'generate'])->name('generate');
        Route::get('/bayar', [PenggajianController::class, 'showBayarForm'])->name('bayar.form');
        Route::post('/bayar', [PenggajianController::class, 'bayar'])->name('bayar');
        Route::get('/{id}/edit', [PenggajianController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PenggajianController::class, 'update'])->name('update');
        Route::delete('/{id}', [PenggajianController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('laporan-penggajian')->name('laporan_penggajian.')->group(function () {
        Route::get('/', [LaporanPenggajianController::class, 'index'])->name('index');
        Route::get('/create', [LaporanPenggajianController::class, 'create'])->name('create');
        Route::post('/', [LaporanPenggajianController::class, 'store'])->name('store');
        Route::get('/{id}', [LaporanPenggajianController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LaporanPenggajianController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LaporanPenggajianController::class, 'update'])->name('update');
        Route::delete('/{id}', [LaporanPenggajianController::class, 'destroy'])->name('destroy');

        // Route untuk generate laporan bulanan
        Route::post('/generate-bulanan', [LaporanPenggajianController::class, 'generateBulanan'])->name('generate_bulanan');

        // Route untuk cetak PDF
        Route::get('/cetak/pdf', [LaporanPenggajianController::class, 'cetakPdf'])->name('cetak_pdf');
    });
});



                        // Endpoints untuk callback Midtrans
        Route::get('/pembayaran/finish/{order_id?}', [PembayaranController::class, 'finish'])
            ->name('pembayaran.finish');
        Route::get('/pembayaran/unfinish', [PembayaranController::class, 'unfinish'])->name('pembayaran.unfinish');
        Route::get('/pembayaran/error', [PembayaranController::class, 'error'])->name('pembayaran.error');
        Route::post('/pembayaran/notification', [PembayaranController::class, 'notification'])->name('pembayaran.notification');


        // Redirect root ke login
        Route::get('/', function () {
            return redirect()->route('login');
        });

        Route::get('/laporan-penggajian/cetak', [LaporanPenggajianController::class, 'cetakPdf'])->name('laporan_penggajian.cetak');
        Route::get('pendapatan', [PendapatanController::class, 'pendapatan'])->name('pendapatan');

        Route::get('/payment/mock/{orderId}', function ($orderId) {
        $pesanan = Pesanan::where('id_transaksi', $orderId)->first();

        if (!$pesanan) {
            abort(404, 'Order not found');
        }

        // Mock payment success after 3 seconds
        $pesanan->update([
            'status_pembayaran' => 'paid',
            'payment_method' => 'mock_payment',
            'paid_at' => now(),
        ]);

        return view('mock-payment', compact('pesanan'));
    });
