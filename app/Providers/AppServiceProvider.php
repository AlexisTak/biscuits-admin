<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
{
    // Rate-limit par IP + par email
    RateLimiter::for('contact', function ($request) {
        return [
            // 10 requêtes par minute par IP
            Limit::perMinute(10)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Trop de requêtes depuis cette adresse IP. Veuillez patienter 1 minute.',
                    ], 429);
                }),

            // 3 requêtes par heure par email
            Limit::perHour(3)
                ->by($request->input('email'))
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vous avez déjà envoyé 3 messages aujourd\'hui. Veuillez patienter.',
                    ], 429);
                }),
        ];
    });
}
}
