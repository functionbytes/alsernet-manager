<?php

namespace Modules\Mailer\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MailerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge module config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/mailer.php',
            'mailer'
        );
    }

    public function boot(): void
    {
        // Load routes with proper prefix and middleware
        $this->registerRoutes();

        // Publish config
        $this->publishes([
            __DIR__.'/../../config/mailer.php' => config_path('mailer.php'),
        ], 'mailer-config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'mailer');
    }

    /**
     * Register module routes
     */
    protected function registerRoutes(): void
    {
        // Web routes for manager settings
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('manager/settings/mailers')
            ->name('manager.settings.mailers.')
            ->group(base_path('modules/Mailer/routes/web.php'));

        // API routes
        Route::middleware(['api', 'throttle:60,1'])
            ->prefix('api/email-endpoints')
            ->name('api.email-endpoints.')
            ->group(base_path('modules/Mailer/routes/api.php'));
    }
}
