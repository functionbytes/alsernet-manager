<?php

namespace Modules\Telescope\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\IncomingEntry;

class TelescopeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only load Telescope in development or if explicitly enabled
        if (!$this->app->environment('production')) {
            Telescope::night();
        }

        // Filter entries to avoid storing too much data
        $this->filterEntries();

        // Configure watchers
        $this->configureWatchers();

        // Register routes
        $this->registerRoutes();
    }

    /**
     * Filter Telescope entries to control what gets recorded
     */
    private function filterEntries(): void
    {
        Telescope::filter(function (IncomingEntry $entry) {
            // Skip recording of successful HTTP requests from assets
            if ($entry->isRequest()) {
                $uri = $entry->content['request']['uri'] ?? '';

                // Skip static assets
                if (preg_match('/\.(js|css|png|jpg|jpeg|gif|svg|webp|ico|woff|woff2|ttf|eot)$/i', $uri)) {
                    return false;
                }

                // Skip health checks and ping routes
                if (preg_match('/\/(health|ping|livewire)/', $uri)) {
                    return false;
                }
            }

            // Skip recording of certain commands
            if ($entry->isCommand()) {
                $command = $entry->content['command'] ?? '';

                // Skip long-running queue workers and schedulers
                if (preg_match('/queue:work|schedule:run|horizon/', $command)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Configure which watchers to enable
     */
    private function configureWatchers(): void
    {
        // Telescope watchers are configured via telescope config file
        // This method is here for future extensibility
    }

    /**
     * Register Telescope routes
     */
    private function registerRoutes(): void
    {
        if (!$this->app->environment('production')) {
            // Load Telescope routes automatically
            Telescope::startRecording();
        }
    }
}
