<?php
// File: config/midtrans.php - Buat file ini jika belum ada
return [
    'server_key' => env('SB-Mid-server-hGu2nY87nIgs3qb-ANZezDh9'),
    'client_key' => env('SB-Mid-client-i03wPNauKIAt9gws'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];
