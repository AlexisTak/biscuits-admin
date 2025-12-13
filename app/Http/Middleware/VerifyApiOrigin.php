<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyApiOrigin
{
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = [
            'http://localhost:4321',
            'https://biscuits.dev',
            'https://www.biscuits.dev',
        ];

        $origin = $request->header('Origin');

        if (!in_array($origin, $allowedOrigins)) {
            return response()->json([
                'success' => false,
                'message' => 'Origin non autorisée.',
            ], 403);
        }

        return $next($request);
    }
}