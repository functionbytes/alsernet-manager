<?php

namespace Modules\Notification\Providers;

use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register Notification services
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/managers.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'notification');

        $this->publishes([
            __DIR__ . '/../../config/notification.php' => config_path('notification.php'),
        ], 'notification-config');
    }
}
