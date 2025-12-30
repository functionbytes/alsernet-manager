<?php

namespace Modules\Documents\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Document';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
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

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        // Manager settings routes
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('manager/settings')
            ->name('manager.settings.')
            ->group(module_path($this->name, 'routes/managers.php'));

        // Administrative routes
        Route::middleware(['web', 'auth', 'role:administrative|super-admin'])
            ->prefix('administrative')
            ->name('administrative.')
            ->group(module_path($this->name, 'routes/administratives.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware(['api', 'throttle:60,1'])
            ->prefix('api/documents')
            ->name('api.')
            ->group(module_path($this->name, 'routes/api.php'));
    }
}
