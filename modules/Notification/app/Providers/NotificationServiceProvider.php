<?php

namespace Modules\Notification\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Notification\Console\Commands\CleanOldNotifications;

class NotificationServiceProvider extends ServiceProvider
{
    protected string $name = 'Notification';

    public function register(): void
    {
        // Merge module config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/notification.php',
            'notification'
        );

        // Register commands
        $this->registerCommands();
    }

    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/managers.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../config/notification.php' => config_path('notification.php'),
        ], 'notification-config');

        // Publish assets (CSS and JavaScript)
        $this->publishes([
            __DIR__.'/../../resources/css' => public_path('modules/Notification/css'),
            __DIR__.'/../../resources/js' => public_path('modules/Notification/js'),
        ], 'notification-assets');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'notification');
    }

    protected function registerCommands(): void
    {
        $this->commands([
            CleanOldNotifications::class,
        ]);
    }
}
