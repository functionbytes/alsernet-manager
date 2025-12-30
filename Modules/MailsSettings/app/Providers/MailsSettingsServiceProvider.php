<?php

namespace Modules\MailsSettings\Providers;

use Illuminate\Support\ServiceProvider;

class MailsSettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge module config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/mails-settings.php',
            'mails-settings'
        );
    }

    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/managers.php');

        // Publish config
        $this->publishes([
            __DIR__.'/../../config/mails-settings.php' => config_path('mails-settings.php'),
        ], 'mails-settings-config');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'mails-settings');
    }
}
