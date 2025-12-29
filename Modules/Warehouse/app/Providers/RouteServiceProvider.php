<?php

namespace Modules\Warehouse\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class RouteServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Warehouse';

    protected string $nameLower = 'warehouse';

    /**
     * Called before routes are registered.
     */
    public function boot(): void
    {
        $this->setNamespace();

        Route::middleware('web')
            ->group(module_path($this->name, 'routes/web.php'));
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapManagerRoutes();

        $this->mapWarehouseWorkerRoutes();

        $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for manager profile.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapManagerRoutes(): void
    {
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('manager/warehouse')
            ->name('manager.warehouse.')
            ->group(module_path($this->name, 'routes/managers.php'));
    }

    /**
     * Define the "web" routes for warehouse worker profile.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWarehouseWorkerRoutes(): void
    {
        Route::middleware(['web', 'auth', 'check.roles.permissions:warehouse'])
            ->prefix('warehouse')
            ->name('warehouse.')
            ->group(module_path($this->name, 'routes/warehouses.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware(['api', 'throttle:60,1'])
            ->prefix('api/warehouse')
            ->name('api.')
            ->group(module_path($this->name, 'routes/api.php'));
    }

    /**
     * Set the namespace for controllers in the module.
     */
    protected function setNamespace(): void
    {
        $this->namespace = 'Modules\\'.$this->name.'\\Http\\Controllers';
    }
}
