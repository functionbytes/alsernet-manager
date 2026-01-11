<?php

namespace Modules\Erp\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Erp\Services\Integrations\ErpService;
use Modules\Theme\Services\NavService;

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

        // Sidebar con los items del módulo
        NavService::registerSidebar('settings', [
            'title' => 'Integración ERP',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'settings.erp.index'],
                ['label' => 'Configuración', 'route' => 'settings.erp.edit'],
            ],
        ]);
    }
}
