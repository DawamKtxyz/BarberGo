<?php
// config/auth.php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],

        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'tukang_cukur',
        ],

        'pelanggan' => [
            'driver' => 'token',
            'provider' => 'pelanggan',
            'hash' => false,
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'tukang_cukur' => [
            'driver' => 'eloquent',
            'model' => App\Models\TukangCukur::class,
        ],

        'pelanggan' => [
            'driver' => 'eloquent',
            'model' => App\Models\Pelanggan::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'tukang_cukur' => [
            'provider' => 'tukang_cukur',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'pelanggan' => [
            'provider' => 'pelanggan',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
