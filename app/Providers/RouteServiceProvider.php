<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

    class RouteServiceProvider extends ServiceProvider
    {
        public function boot(): void
        {
            $this->configureRateLimiting();

            $this->routes(function () {
                Route::middleware('api')
                    ->prefix('api')
                    ->group(base_path('routes/api.php'));

                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            });
        }

    protected function configureRateLimiting(): void
    {
        // Rate limiter pour formulaire de contact : 5 requêtes/minute par IP
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Trop de tentatives. Veuillez patienter quelques instants.',
                    ], 429);
                });
        });

        // Rate limiter API générale
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}