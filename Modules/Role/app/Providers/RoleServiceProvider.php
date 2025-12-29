<?php

namespace Modules\Role\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Role\Providers\PermissionBladeServiceProvider;

class RoleServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register PermissionBladeServiceProvider
        $this->app->register(PermissionBladeServiceProvider::class);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/managers.php');

        $this->publishes([
            __DIR__ . '/../../config/role.php' => config_path('role.php'),
        ], 'role-config');
    }
}
