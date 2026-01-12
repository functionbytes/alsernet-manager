<?php

namespace Modules\Supplier\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Supplier';

    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        // Settings routes
        Route::middleware(['web', 'auth', 'role:super-admin'])
            ->group(module_path($this->name, 'routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/suppliers')
            ->name('api.suppliers.')
            ->group(module_path($this->name, 'routes/api.php'));
    }
}
