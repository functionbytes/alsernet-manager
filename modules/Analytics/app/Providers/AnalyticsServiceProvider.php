<?php

namespace Modules\Analytics\Providers;

use App\Services\NavService;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Modules\Analytics\Analytics;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Analytics singleton
        $this->app->singleton('analytics', function ($app) {
            $propertyId = setting('google_analytics_property_id', '');
            $credentials = setting('google_analytics_credentials', '');

            return new Analytics($propertyId, $credentials);
        });
    }

    /**
     * Boot services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'analytics');

        // Register facade alias
        Facade::aliasNamespace('Analytics', 'Modules\\Analytics\\Facades');

        // Register menus
        $this->registerMenus();
    }

    /**
     * Registrar menús del módulo Analytics
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para Analytics
        NavService::registerMiniItem('analytics', [
            'icon' => 'fa-chart-line',
            'tooltip' => 'Analítica',
            'sidebar_id' => 'analytics',
            'order' => 50,
        ]);

        // Sidebar con los items del módulo
        NavService::registerSidebar('analytics', [
            'title' => 'Analítica',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'analytics.dashboard', 'icon' => 'fa-tachometer-alt'],
                ['label' => 'Configuración', 'route' => 'settings.analytics.index', 'icon' => 'fa-cog'],
            ],
        ]);
    }
}
