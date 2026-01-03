<?php

namespace Modules\System\Providers;

use App\Services\NavService;
use Illuminate\Support\ServiceProvider;
use Modules\System\Services\SystemInfoService;

class SystemServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register SystemInfoService as singleton
        $this->app->singleton(SystemInfoService::class, function ($app) {
            return new SystemInfoService;
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'system');

        // Publish config
        $this->publishes([
            __DIR__.'/../../config/system.php' => config_path('system.php'),
        ], 'system-config');

        // Register menus
        $this->registerMenus();
    }

    /**
     * Registrar menús del módulo System
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para System
        NavService::registerMiniItem('system', [
            'icon' => 'fa-cogs',
            'tooltip' => 'Sistema',
            'sidebar_id' => 'system',
            'order' => 110,
        ]);

        // Sidebar con los items del módulo
        NavService::registerSidebar('system', [
            'title' => 'Sistema',
            'items' => [
                ['label' => 'Configuración', 'route' => 'settings.system.index'],
                ['label' => 'Información', 'route' => 'settings.system.info.index'],
                ['label' => 'Cache y mantenimiento', 'route' => 'settings.system.cache.index'],
                ['label' => 'Supervisor', 'route' => 'settings.system.supervisor.index'],
                ['label' => 'Logs del servidor', 'route' => 'settings.system.access.index'],
                ['label' => 'Carga de archivos', 'route' => 'settings.system.uploading.index'],
                ['label' => 'Modo mantenimiento', 'route' => 'settings.system.maintenance.index'],
            ],
        ]);
    }
}
