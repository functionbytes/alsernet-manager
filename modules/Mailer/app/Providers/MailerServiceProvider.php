<?php

namespace Modules\Mailer\Providers;

use App\Services\NavService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Mailer\Policies\MailerSettingsPolicy;

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

        // Register authorization gates
        $this->registerGates();

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
        // Load web routes (settings admin panel)
        require module_path('Mailer', 'routes/web.php');

        // Public API routes (email sending endpoints)
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
                ['label' => 'Plantillas', 'route' => 'mailers.templates.index'],
                ['label' => 'Componentes', 'route' => 'mailers.components.index'],
                ['label' => 'Variables', 'route' => 'mailers.variables.index'],
                ['label' => 'Puntos de envío', 'route' => 'mailers.endpoints.index'],
            ],
        ]);
    }

    /**
     * Register authorization gates for mailer settings
     */
    protected function registerGates(): void
    {
        $settingsPolicy = new MailerSettingsPolicy;

        // Configure mailer settings gates
        Gate::define('configure-mailers', fn ($user) => $settingsPolicy->configure($user));
        Gate::define('view-mailer-settings', fn ($user) => $settingsPolicy->viewSettings($user));
        Gate::define('manage-mailer-templates', fn ($user) => $settingsPolicy->manageTemplates($user));
        Gate::define('manage-mailer-components', fn ($user) => $settingsPolicy->manageComponents($user));
        Gate::define('manage-mailer-variables', fn ($user) => $settingsPolicy->manageVariables($user));
        Gate::define('manage-mailer-endpoints', fn ($user) => $settingsPolicy->manageEndpoints($user));
    }
}
