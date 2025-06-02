<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarberAuthController;
use App\Http\Controllers\Api\BarberProfileController;
use App\Http\Controllers\Api\JadwalBarberController;
use App\Http\Controllers\Api\BarberController;
use App\Http\Controllers\Api\PesananController;
use App\Http\Controllers\API\PelangganController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PenggajianController;

// Test routes for debugging
Route::get('/test', [TestController::class, 'index']);
Route::get('/cors-test', [TestController::class, 'corsTest']);
Route::options('/cors-test', [TestController::class, 'corsTest']);

// Preflight request handling for CORS
Route::options('/{any}', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
})->where('any', '.*');

// Barber routes
Route::prefix('barber')->middleware('api')->group(function () {
    Route::post('/login', [BarberAuthController::class, 'login']);
    Route::post('/register', [BarberAuthController::class, 'register']);
    Route::post('/check-verification', [BarberAuthController::class, 'checkVerificationStatus']);


    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Profile management
        Route::get('/profile', [BarberProfileController::class, 'getProfile']);
        Route::post('/update-profile', [BarberProfileController::class, 'updateProfile']);
        Route::post('/upload-profile-photo', [BarberProfileController::class, 'uploadProfilePhoto']);
        Route::post('/logout', [BarberAuthController::class, 'logout']);

        // Schedule management
        Route::get('/schedules', [JadwalBarberController::class, 'getMySchedules']);
        Route::get('/bookings', [JadwalBarberController::class, 'getMyBookings']);
        Route::post('/schedules/add', [JadwalBarberController::class, 'addTimeSlot']);
        Route::delete('/schedules/{id}', [JadwalBarberController::class, 'deleteTimeSlot']);
        Route::get('/schedules/available', [JadwalBarberController::class, 'getAvailableSlots']);
        Route::post('/schedules/bulk-add', [JadwalBarberController::class, 'bulkAddTimeSlots']);

        // Bulk delete schedules
        Route::delete('/jadwal/multiple-delete', [JadwalBarberController::class, 'deleteMultiple']);

        // Statistics
        Route::get('/stats/{barberId}', [BarberController::class, 'getStats']);

        // TAMBAHKAN ROUTE PENGGAJIAN DI SINI:
        Route::get('/penggajian', [PenggajianController::class, 'getPenggajianBarber']);
        Route::get('/penggajian/stats', [PenggajianController::class, 'getStatsPenggajian']);
        Route::get('/penggajian/{id}', [PenggajianController::class, 'showPenggajian']);
    });
});

// Pelanggan (Customer) routes
Route::prefix('pelanggan')->group(function () {
    Route::post('/register', [PelangganController::class, 'register']);
    Route::post('/login', [PelangganController::class, 'login']);

    // Protected routes - using pelanggan guard
    Route::middleware('auth:pelanggan')->group(function () {
         // Profile management
        Route::get('/profile', [PelangganController::class, 'profile']);
        Route::post('/profile/update', [PelangganController::class, 'updateProfile']);
        Route::post('/upload-profile-photo', [PelangganController::class, 'uploadProfilePhoto']);
        Route::post('/logout', [PelangganController::class, 'logout']);

        // Booking management
        Route::post('/bookings', [PesananController::class, 'store']);
        Route::get('/bookings', [PesananController::class, 'getMyBookings']);
        Route::get('/bookings/{id}', [PesananController::class, 'show']);
        Route::delete('/bookings/{id}', [PesananController::class, 'cancel']);
        Route::get('/bookings-stats', [PesananController::class, 'getMyStats']);
    });
});

// Payment routes (accessible by authenticated pelanggan)
Route::middleware('auth:pelanggan')->prefix('payment')->group(function () {
    Route::post('/create/{bookingId}', [PaymentController::class, 'createPayment']);
    Route::get('/status/{orderId}', [PaymentController::class, 'checkStatus']);
    Route::get('/history', [PaymentController::class, 'getPaymentHistory']);
});

// Payment webhook (no authentication required)
Route::post('/payment/notification', [PaymentController::class, 'notification']);

// Test route untuk cek Midtrans connection
Route::get('/test-midtrans', function() {
    try {
        // Check if Midtrans config exists
        $serverKey = config('SB-Mid-server-hGu2nY87nIgs3qb-ANZezDh9');
        $clientKey = config('SB-Mid-client-i03wPNauKIAt9gws');

        if (empty($serverKey) || empty($clientKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Midtrans configuration not found in .env',
                'config' => [
                    'server_key_exists' => !empty($serverKey),
                    'client_key_exists' => !empty($clientKey),
                ]
            ], 400);
        }

        // Test Midtrans Config loading
        \Midtrans\Config::$serverKey = $serverKey;
        \Midtrans\Config::$isProduction = config('midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized', true);
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds', true);

        return response()->json([
            'success' => true,
            'message' => 'Midtrans configuration loaded successfully',
            'config' => [
                'server_key_length' => strlen($serverKey),
                'client_key_length' => strlen($clientKey),
                'is_production' => config('midtrans.is_production', false),
                'is_sanitized' => config('midtrans.is_sanitized', true),
                'is_3ds' => config('midtrans.is_3ds', true),
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Midtrans configuration error',
            'error' => $e->getMessage(),
        ], 500);
    }
});

// Chat routes (accessible by both barber and pelanggan)
Route::middleware(['auth:sanctum,pelanggan'])->group(function () {
    Route::get('/chats', [ChatController::class, 'getChats']);
    Route::get('/chats/{chatId}', [ChatController::class, 'getChatById']);
    Route::post('/chats/direct', [ChatController::class, 'getOrCreateDirectChat']); // New route untuk direct chat
    Route::post('/chats/send', [ChatController::class, 'sendMessage']);

    // Legacy route untuk backward compatibility
    Route::get('/chats/booking/{bookingId}', [ChatController::class, 'getChatByBooking']);
});

// Public routes for barber discovery (accessible by anyone, including customers)
Route::prefix('public')->group(function () {
    // Barber discovery
    Route::get('/barbers', [BarberController::class, 'index']);
    Route::get('/barbers/{id}', [BarberController::class, 'show']);
    Route::get('/barbers/{id}/slots', [BarberController::class, 'getAvailableSlots']);
    Route::get('/specializations', [BarberController::class, 'getSpecializations']);
});

// Alternative authenticated routes for customers to access barber info
Route::middleware('auth:pelanggan')->group(function () {
    Route::get('/barbers', [BarberController::class, 'index']);
    Route::get('/barbers/{id}', [BarberController::class, 'show']);
    Route::get('/barbers/{id}/slots', [BarberController::class, 'getAvailableSlots']);
    Route::get('/specializations', [BarberController::class, 'getSpecializations']);
});
