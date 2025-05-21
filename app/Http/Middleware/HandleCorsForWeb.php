<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HandleCorsForWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Log request information for debugging
        Log::info('CORS Middleware: Request received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'origin' => $request->header('Origin'),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
        ]);

        // Handle OPTIONS (preflight) requests
        if ($request->isMethod('OPTIONS')) {
            Log::info('CORS Middleware: Handling preflight OPTIONS request');

            // Create a response for preflight
            $response = response('', 200);
        } else {
            // Process the normal request
            $response = $next($request);

            Log::info('CORS Middleware: Request processed', [
                'status' => $response->status(),
                'content_type' => $response->headers->get('Content-Type'),
            ]);
        }

        // Get the origin or default to *
        $origin = $request->header('Origin') ?: '*';
        Log::info('CORS Middleware: Setting Access-Control-Allow-Origin to ' . $origin);

        // Add CORS headers
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, Accept, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours

        // Log the response headers for debugging
        Log::info('CORS Middleware: Response headers set', [
            'allow_origin' => $response->headers->get('Access-Control-Allow-Origin'),
            'allow_methods' => $response->headers->get('Access-Control-Allow-Methods'),
            'allow_headers' => $response->headers->get('Access-Control-Allow-Headers'),
        ]);

        return $response;
    }
}
