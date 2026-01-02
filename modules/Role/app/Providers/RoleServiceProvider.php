<?php

namespace Modules\Role\Providers;

use App\Services\NavService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RoleServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register PermissionBladeServiceProvider
        $this->app->register(PermissionBladeServiceProvider::class);
    }

    public function boot()
    {
        // Register routes with manager prefix
        $this->registerRoutes();

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'role');

        $this->publishes([
            __DIR__.'/../../config/role.php' => config_path('role.php'),
        ], 'role-config');

        // Register navigation menus
        $this->registerMenus();
    }

    /**
     * Register Role module routes
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('settings')
            ->name('manager.settings.')
            ->group(function (): void {
                // Load view routes (GET)
                require __DIR__.'/../../routes/web.php';

                // Load API routes (POST, PUT, DELETE) - same prefix/name
                require __DIR__.'/../../routes/api.php';
            });
    }

    /**
     * Register navigation menus for the Role module
     */
    protected function registerMenus(): void
    {
        // Mini-nav item for Roles & Permissions
        NavService::registerMiniItem('roles', [
            'icon' => 'fa-shield',
            'tooltip' => 'Roles y Permisos',
            'sidebar_id' => 'roles',
            'order' => 40,
        ]);

        // Sidebar with menu items
        NavService::registerSidebar('roles', [
            'title' => 'Roles y Permisos',
            'items' => [
                ['label' => 'Roles', 'route' => 'manager.settings.roles.index'],
                ['label' => 'Permisos', 'route' => 'manager.settings.permissions.index'],
            ],
        ]);
    }
}
