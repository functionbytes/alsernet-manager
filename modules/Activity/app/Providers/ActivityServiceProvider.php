<?php

namespace Modules\Activity\Providers;

use App\Services\NavService;
use Illuminate\Support\ServiceProvider;

class ActivityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerConfigurations();

        // Register menus
        $this->registerMenus();
    }

    protected function registerConfigurations(): void
    {
        $this->publishes([
            __DIR__.'/../../config/activity.php' => config_path('activity.php'),
        ], 'config');
    }

    /**
     * Registrar menús del módulo Activity
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para Activity
        NavService::registerMiniItem('activity', [
            'icon' => 'fa-history',
            'tooltip' => 'Actividad',
            'sidebar_id' => 'activity',
            'order' => 85,
        ]);

        // Sidebar con los items del módulo
        NavService::registerSidebar('activity', [
            'title' => 'Historial de actividad',
            'items' => [
                ['label' => 'Registro de cambios', 'route' => 'activity.logs', 'icon' => 'fa-list'],
                ['label' => 'Auditoría', 'route' => 'activity.audit', 'icon' => 'fa-shield-alt'],
            ],
        ]);
    }
}
