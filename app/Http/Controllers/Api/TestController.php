<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function index(Request $request)
    {
        // Log request information
        Log::info('Test API endpoint called', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'origin' => $request->header('Origin', 'unknown'),
        ]);

        // Generate response for testing
        $response = [
            'success' => true,
            'message' => 'API connection test successful',
            'time' => now()->toDateTimeString(),
            'request' => [
                'method' => $request->method(),
                'origin' => $request->header('Origin', 'unknown'),
                'user_agent' => $request->header('User-Agent', 'unknown'),
            ],
            'server' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => config('app.env'),
            ],
        ];

        Log::info('Test API response generated', ['response' => $response]);

        return response()->json($response);
    }

    public function corsTest(Request $request)
    {
        // Log CORS test request
        Log::info('CORS test endpoint called', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'origin' => $request->header('Origin', 'unknown'),
        ]);

        // Generate response for CORS testing
        $response = [
            'success' => true,
            'message' => 'CORS test successful',
            'timestamp' => now()->toDateTimeString(),
            'request_headers' => [
                'origin' => $request->header('Origin', 'unknown'),
                'content_type' => $request->header('Content-Type', 'unknown'),
                'accept' => $request->header('Accept', 'unknown'),
                'x_requested_with' => $request->header('X-Requested-With', 'unknown'),
            ],
            'environment' => [
                'app_env' => config('app.env'),
                'app_debug' => config('app.debug'),
            ],
        ];

        Log::info('CORS test response generated', ['response' => $response]);

        return response()->json($response);
    }
}
