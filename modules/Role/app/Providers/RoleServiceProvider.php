<?php

namespace Modules\Role\Providers;

use App\Services\NavService;
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
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
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
                ['label' => 'Roles', 'route' => 'roles'],
                ['label' => 'Permisos', 'route' => 'permissions'],
            ],
        ]);
    }
}
