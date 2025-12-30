<?php

namespace Modules\Backup\Providers;

use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register Backup services
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/managers.php');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'backup');

        $this->publishes([
            __DIR__ . '/../../config/backup.php' => config_path('backup.php'),
        ], 'backup-config');
    }
}
