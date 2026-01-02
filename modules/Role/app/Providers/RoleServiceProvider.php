<?php

namespace Modules\Role\Providers;

use App\Services\NavService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RoleServiceProvider extends ServiceProvider
{
    protected string $name = 'Role';

    public function register()
    {
        // Register PermissionBladeServiceProvider
        $this->app->register(PermissionBladeServiceProvider::class);
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'role');

        $this->publishes([
            __DIR__.'/../../config/role.php' => config_path('role.php'),
        ], 'role-config');

        // Register navigation menus
        $this->registerMenus();

        // Register routes after all providers have booted
        $this->booted(function () {
            $this->registerRoutes();
        });
    }

    /**
     * Register routes for the Role module
     */
    protected function registerRoutes(): void
    {
        // Manager settings routes (GET views + POST/PUT/DELETE API)
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('settings')
            ->name('settings.')
            ->group(function () {
                // Load view routes (GET)
                require module_path($this->name, 'routes/web.php');

                // Load API routes (POST, PUT, DELETE) if exists
                if (file_exists(module_path($this->name, 'routes/api.php'))) {
                    require module_path($this->name, 'routes/api.php');
                }
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
                ['label' => 'Roles', 'route' => 'settings.roles.index'],
                ['label' => 'Permisos', 'route' => 'settings.permissions.index'],
            ],
        ]);
    }
}
