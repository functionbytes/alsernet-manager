<?php

namespace Modules\Event\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapManagerRoutes();
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api')
            ->name('api.')
            ->group(module_path('Event', 'routes/api.php'));
    }

    protected function mapManagerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('manager')
            ->name('manager.')
            ->group(module_path('Event', 'routes/web.php'));
    }
}
