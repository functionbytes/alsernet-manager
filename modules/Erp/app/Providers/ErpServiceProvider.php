<?php

namespace Modules\Erp\Providers;

use App\Services\NavService;
use Illuminate\Support\ServiceProvider;
use Modules\Erp\Services\Integrations\ErpService;

class ErpServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register ERP Service as singleton
        $this->app->singleton(ErpService::class, function ($app) {
            return new ErpService;
        });

        // Register Facade alias
        $this->app->alias('erp', ErpService::class);
    }

    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/erp.php' => config_path('erp.php'),
        ], 'erp-config');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        // Register menus
        $this->registerMenus();
    }

    /**
     * Registrar menús del módulo Erp
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para ERP
        NavService::registerMiniItem('erp', [
            'icon' => 'fa-server',
            'tooltip' => 'Integración ERP',
            'sidebar_id' => 'erp',
            'order' => 90,
        ]);

        // Sidebar con los items del módulo
        NavService::registerSidebar('erp', [
            'title' => 'Integración ERP',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'settings.erp.index', 'icon' => 'fa-tachometer-alt'],
                ['label' => 'Configuración', 'route' => 'settings.erp.edit', 'icon' => 'fa-cog'],
            ],
        ]);
    }
}
