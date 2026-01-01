<?php

namespace Modules\Supplier\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions that use the traditional queue syntax.
     *
     * @var string
     */
    protected $namespace = 'modules\Supplier\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
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
        $this->mapManagerRoutes();
    }

    /**
     * Define the API routes for the application.
     *
     * These routes are stateless, use the "api" middleware group, and are protected by Sanctum authentication.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api')
            ->name('api.')
            ->group(base_path('modules/Supplier/routes/api.php'));
    }

    /**
     * Define the manager routes for the application.
     */
    protected function mapManagerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('manager')
            ->name('manager.')
            ->group(base_path('modules/Supplier/routes/managers.php'));
    }
}
