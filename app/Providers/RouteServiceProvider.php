<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // ✅ IMPORTANTE: Document module registra sus propias rutas sin middleware 'api'
            // para permitir acceso desde sistemas externos (PrestaShop)
            // Las rutas del Document module NO van aquí, se registran en modules/Document/app/Providers/RouteServiceProvider.php

            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api/api.php'));

            Route::middleware('web')
                ->group(function () {
                    Route::group([], base_path('routes/web.php'));
                });
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
