<?php

namespace Modules\Notification\Providers;

use App\Services\NavService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Notification\Console\Commands\CleanOldNotifications;

class NotificationServiceProvider extends ServiceProvider
{
    protected string $name = 'Notification';

    protected string $nameLower = 'notification';

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

        // Register routes
        $this->registerRoutes();

        // Register menus
        $this->registerMenus();
    }

    /**
     * Register routes for the Notification module
     */
    protected function registerRoutes(): void
    {
        $modulePath = dirname(__DIR__, 2);

        // Notification operational routes
        Route::middleware(['web', 'auth'])
            ->prefix('notifications')
            ->name('notifications.')
            ->group(function () use ($modulePath) {
                require $modulePath.'/routes/web.php';
            });

        // Notification backups routes
        Route::middleware(['web', 'auth', 'role:super-admin'])
            ->prefix('settings/notifications')
            ->name('settings.notifications.')
            ->group(function () use ($modulePath) {
                require $modulePath.'/routes/settings.php';
            });

        // API routes
        Route::middleware(['web', 'auth'])
            ->prefix('api/notifications')
            ->name('api.notifications.')
            ->group(function () use ($modulePath) {
                require $modulePath.'/routes/api.php';
            });
    }

    protected function registerCommands(): void
    {
        $this->commands([
            CleanOldNotifications::class,
        ]);
    }

    /**
     * Registrar menús del módulo Notification
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para Notifications
        NavService::registerMiniItem('notifications', [
            'icon' => 'fa-bell',
            'tooltip' => 'Notificaciones',
            'sidebar_id' => 'notifications',
            'order' => 70,
        ]);

        // Sidebar con los items del módulo
        NavService::registerSidebar('notifications', [
            'title' => 'Notificaciones',
            'items' => [
                ['label' => 'Todas las notificaciones', 'route' => 'notifications.index'],
            ],
        ]);
    }
}
