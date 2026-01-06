<?php

namespace Modules\Core\Providers;

use App\Services\NavService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();

        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->loadViewsFrom(module_path($this->moduleName, 'resources/views'), $this->moduleNameLower);

        // Register routes after all providers have booted
        $this->booted(function () {
            $this->registerRoutes();
        });

        // Register menus
        $this->registerMenus();
    }

    protected function registerRoutes(): void
    {
        // Register Core dashboard route
        Route::middleware(['web', 'auth'])
            ->name('core.')
            ->group(function () {
                Route::get('/dashboard', [\Modules\Core\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
            });
    }

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        $this->loadTranslationsFrom(module_path($this->moduleName, 'resources/lang'), $this->moduleNameLower);
    }

    protected function registerConfig(): void
    {
        // Register config files from module if they exist
        $configPath = module_path($this->moduleName, 'config');
        if (is_dir($configPath)) {
            foreach (glob($configPath.'/*.php') as $configFile) {
                $this->publishes([
                    $configFile => config_path(basename($configFile)),
                ], $this->moduleNameLower.'-config');
            }
        }
    }

    /**
     * Registrar menús del módulo Core
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para Core/Dashboard
        NavService::registerMiniItem('dashboard', [
            'icon' => 'fa-home',
            'tooltip' => 'Panel de control',
            'sidebar_id' => 'dashboard',
            'order' => 10,
        ]);

        // Sidebar con los items del módulo
        NavService::registerSidebar('dashboard', [
            'title' => 'Panel de control',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'core.dashboard'],
            ],
        ]);
    }
}
