<?php

namespace Modules\Backup\Providers;

use App\Services\NavService;
use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    public function boot(): void
    {
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

        NavService::registerSidebar('settings', [
            'title' => 'Copias de seguridad',
            'items' => [
                ['label' => 'Todas las copias', 'route' => 'settings.backups.index'],
                ['label' => 'Programación', 'route' => 'settings.backup.schedules.index'],
            ],
        ]);
    }
}
