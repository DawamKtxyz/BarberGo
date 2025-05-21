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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

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

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Profile management
        Route::get('/profile', [BarberProfileController::class, 'getProfile']);
        Route::post('/update-profile', [BarberProfileController::class, 'updateProfile']);
        Route::post('/upload-profile-photo', [BarberProfileController::class, 'uploadProfilePhoto']); // New route
        Route::post('/logout', [BarberAuthController::class, 'logout']);

        // Schedule management
        Route::get('/schedules', [JadwalBarberController::class, 'getMySchedules']);
        Route::get('/bookings', [JadwalBarberController::class, 'getMyBookings']);
        Route::post('/schedules/add', [JadwalBarberController::class, 'addTimeSlot']);
        Route::delete('/schedules/{id}', [JadwalBarberController::class, 'deleteTimeSlot']);
        Route::get('/schedules/available', [JadwalBarberController::class, 'getAvailableSlots']);
        Route::post('/schedules/bulk-add', [JadwalBarberController::class, 'bulkAddTimeSlots']);

        // Statistics
        Route::get('/stats/{barberId}', [BarberController::class, 'getStats']);
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
        Route::post('/upload-profile-photo', [PelangganController::class, 'uploadProfilePhoto']); // New route
        Route::post('/logout', [PelangganController::class, 'logout']);

        // Booking management
        Route::post('/bookings', [PesananController::class, 'store']);
        Route::get('/bookings', [PesananController::class, 'getMyBookings']);
        Route::get('/bookings/{id}', [PesananController::class, 'show']);
        Route::delete('/bookings/{id}', [PesananController::class, 'cancel']);
        Route::get('/bookings-stats', [PesananController::class, 'getMyStats']);
    });
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
