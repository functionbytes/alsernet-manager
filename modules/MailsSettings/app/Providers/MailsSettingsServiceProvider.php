<?php

namespace Modules\MailsSettings\Providers;

use App\Services\NavService;
use Illuminate\Support\ServiceProvider;

class MailsSettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge module config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/mails-settings.php',
            'mails-backups'
        );
    }

    public function boot(): void
    {
        // Register routes
        $this->registerRoutes();

        // Register navigation
        $this->registerMenus();

        // Publish config
        $this->publishes([
            __DIR__.'/../../config/mails-settings.php' => config_path('mails-backups.php'),
        ], 'mails-backups-config');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'mails-backups');
    }

    protected function registerRoutes(): void
    {
        $modulePath = dirname(__DIR__, 2);

        // Load routes directly without wrapping (routes already have their own groups)
        require $modulePath.'/routes/web.php';
    }

    protected function registerMenus(): void
    {

        NavService::registerSidebar('settings', [
            'title' => 'Configuración de Email',
            'items' => [
                ['label' => 'Configuración general', 'route' => 'settings.email.index'],
                ['label' => 'Email entrante', 'route' => 'settings.incoming-email.index'],
                ['label' => 'Email saliente', 'route' => 'settings.outgoing-email.index'],
            ],
        ]);
    }
}
