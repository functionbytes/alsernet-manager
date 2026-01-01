<?php

namespace Modules\Mailer\Providers;

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
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/managers.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../config/mailer.php' => config_path('mailer.php'),
        ], 'mailer-config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'mailer');
    }
}
