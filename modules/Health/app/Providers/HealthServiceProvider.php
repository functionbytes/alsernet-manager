<?php

namespace Modules\Health\Providers;

use App\Services\NavService;
use Illuminate\Support\ServiceProvider;
use Modules\Health\Checks\DatabaseCheck;
use Modules\Health\Checks\RedisCheck;
use Modules\Health\Checks\StorageCheck;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\QueueCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;

class HealthServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register HealthCheck services
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'health');

        // Publish config
        $this->publishes([
            __DIR__.'/../../config/health.php' => config_path('health.php'),
        ], 'health-config');

        // Register Spatie Health Checks
        $this->registerHealthChecks();

        // Register menus
        $this->registerMenus();
    }

    protected function registerHealthChecks()
    {
        Health::checks([
            // Environment & Configuration
            EnvironmentCheck::new()->expectEnvironment(app()->environment()),
            DebugModeCheck::new()->if(app()->environment('production')),
            OptimizedAppCheck::new(),

            // Database
            DatabaseCheck::new(),
            DatabaseConnectionCountCheck::new()
                ->warnWhenMoreConnectionsThan(50)
                ->failWhenMoreConnectionsThan(100),

            // Cache & Redis
            CacheCheck::new(),
            RedisCheck::new(),

            // Queue System
            QueueCheck::new()->if(config('queue.default') !== 'sync'),

            // Scheduled Tasks
            ScheduleCheck::new()
                ->heartbeatMaxAgeInMinutes(2),

            // Storage
            StorageCheck::new(),
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(70)
                ->failWhenUsedSpaceIsAbovePercentage(90),
        ]);
    }

    /**
     * Registrar menús del módulo HealthCheck
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para Health Check
        NavService::registerMiniItem('health', [
            'icon' => 'fa-heart-pulse',
            'tooltip' => 'Estado del sistema',
            'sidebar_id' => 'health',
            'order' => 100,
        ]);

        // Sidebar con los items del módulo
        NavService::registerSidebar('health', [
            'title' => 'Estado del sistema',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'settings.health.index', 'icon' => 'fa-tachometer-alt'],
                ['label' => 'Historial', 'route' => 'settings.health.history', 'icon' => 'fa-clock-rotate-left'],
            ],
        ]);
    }
}
