<?php

namespace Modules\Document\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Document';

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
        // Operational document routes
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('documents')
            ->name('documents.')
            ->group(module_path($this->name, 'routes/web.php'));

        // Settings VIEW routes (GET only) + API routes (POST, PUT, DELETE)
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('settings/documents')
            ->name('settings.documents.')
            ->group(function () {
                // Load configuration routes (GET)
                require module_path($this->name, 'routes/settings.php');

            });

        // Administrative routes
        Route::middleware(['web', 'auth', 'role:administrative|super-admin'])
            ->prefix('administrative')
            ->name('administrative.')
            ->group(module_path($this->name, 'routes/administratives.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware(['api', 'throttle:60,1'])
            ->prefix('api/documents')
            ->name('api.')
            ->group(module_path($this->name, 'routes/api.php'));
    }
}
