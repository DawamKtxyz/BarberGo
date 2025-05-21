<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->peran === 'admin') {
            return $next($request);
        }

        return redirect()->route('login')->with('error', 'Akses ditolak. Anda tidak memiliki izin admin.');
    }
}
