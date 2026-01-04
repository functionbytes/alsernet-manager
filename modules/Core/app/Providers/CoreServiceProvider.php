<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Debug: write to file to confirm boot is called
        file_put_contents(storage_path('logs/core-provider-boot.log'), 'Core provider boot called at '.date('Y-m-d H:i:s')."\n", FILE_APPEND);

        $this->registerTranslations();
        $this->registerConfig();
        $this->registerRoutes();

        file_put_contents(storage_path('logs/core-provider-boot.log'), 'Core routes registered'."\n", FILE_APPEND);

        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->loadViewsFrom(module_path($this->moduleName, 'resources/views'), $this->moduleNameLower);
    }

    protected function registerRoutes(): void
    {
        // Register Core dashboard route using router instance
        $this->app['router']->group([
            'middleware' => ['web', 'auth'],
            'name' => 'core.',
        ], function ($router) {
            $router->get('/', [\Modules\Core\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        });
    }

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        $this->loadTranslationsFrom(module_path($this->moduleName, 'resources/lang'), $this->moduleNameLower);
    }

    public function provides(): array
    {
        return [];
    }

    protected function registerConfig(): void
    {
        //
    }
}
