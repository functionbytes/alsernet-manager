<?php

namespace Modules\Users\Providers;

use Illuminate\Support\ServiceProvider;

class UsersServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register Users services
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/managers.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'users');

        $this->publishes([
            __DIR__ . '/../../config/users.php' => config_path('users.php'),
        ], 'users-config');
    }
}
