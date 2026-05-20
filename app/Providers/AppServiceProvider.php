<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // Login: 10 intentos por minuto por IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // API general: 120 requests/minuto por usuario autenticado (o IP si guest)
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(30)->by($request->ip());
        });

        // Respuestas: 60 por minuto por usuario (modo kiosko)
        RateLimiter::for('respuestas', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?? $request->ip());
        });

        // Exportar: 5 por minuto por usuario
        RateLimiter::for('exportar', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?? $request->ip());
        });
    }
}
