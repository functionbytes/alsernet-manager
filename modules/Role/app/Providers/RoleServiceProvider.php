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
        // Routes are now loaded from routes/managers.php
        // See: routes/managers.php line 531-532

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'role');

        $this->publishes([
            __DIR__.'/../../config/role.php' => config_path('role.php'),
        ], 'role-config');

        // Register navigation menus
        $this->registerMenus();
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
