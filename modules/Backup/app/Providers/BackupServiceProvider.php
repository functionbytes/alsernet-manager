<?php

namespace Modules\Backup\Providers;

use App\Services\NavService;
use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register Backup services
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'backup');

        $this->publishes([
            __DIR__.'/../../config/backup.php' => config_path('backup.php'),
        ], 'backup-config');

        $this->registerMenus();
    }

    /**
     * Registrar menús del módulo Backup
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para Backups
        NavService::registerMiniItem('backups', [
            'icon' => 'fa-database',
            'tooltip' => 'Copias de seguridad',
            'sidebar_id' => 'backups',
            'order' => 80,
        ]);

        // Sidebar con los items del módulo
        NavService::registerSidebar('backups', [
            'title' => 'Copias de seguridad',
            'items' => [
                ['label' => 'Todas las copias', 'route' => 'settings.backups.index'],
                ['label' => 'Programación', 'route' => 'settings.backup-schedules.index'],
            ],
        ]);
    }
}
