<?php

namespace Modules\Mailer\Providers;

use App\Services\NavService;
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

        // Register navigation menus
        $this->registerMenus();

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
            ->group(module_path('Mailer', 'routes/web.php'));

        // API routes
        Route::middleware(['api', 'throttle:60,1'])
            ->prefix('api/email-endpoints')
            ->name('api.email-endpoints.')
            ->group(module_path('Mailer', 'routes/api.php'));
    }

    /**
     * Register navigation menus for the Mailer module
     */
    protected function registerMenus(): void
    {
        // Mini-nav item for Mailers
        NavService::registerMiniItem('mailers', [
            'icon' => 'fa-envelope',
            'tooltip' => 'Emails',
            'sidebar_id' => 'mailers',
            'order' => 25,
        ]);

        // Sidebar with menu items
        NavService::registerSidebar('mailers', [
            'title' => 'Emails',
            'items' => [
                ['label' => 'Plantillas', 'route' => 'manager.settings.mailers.templates.index'],
                ['label' => 'Componentes', 'route' => 'manager.settings.mailers.components.index'],
                ['label' => 'Variables', 'route' => 'manager.settings.mailers.variables.index'],
                ['label' => 'Puntos de envío', 'route' => 'manager.settings.mailers.endpoints.index'],
            ],
        ]);
    }
}
