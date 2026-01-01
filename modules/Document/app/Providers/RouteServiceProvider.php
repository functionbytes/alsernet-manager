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
        // Manager settings VIEW routes (GET only) + API routes (POST, PUT, DELETE)
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('manager/settings')
            ->name('manager.settings.')
            ->group(function () {
                // Load view routes (GET)
                require module_path($this->name, 'routes/web.php');

                // Load API routes (POST, PUT, DELETE) - same prefix/name
                require module_path($this->name, 'routes/api/settings.php');
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
