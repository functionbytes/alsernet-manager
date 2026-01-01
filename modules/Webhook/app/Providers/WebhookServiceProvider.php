<?php

namespace Modules\Webhook\Providers;

use Illuminate\Support\ServiceProvider;

class WebhookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            'webhook'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('webhook.php'),
        ], 'webhook-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'webhook-migrations');
    }
}
