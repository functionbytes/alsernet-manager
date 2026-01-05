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
        $this->registerTranslations();
        $this->registerConfig();

        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->loadViewsFrom(module_path($this->moduleName, 'resources/views'), $this->moduleNameLower);

        // Register routes after all providers have booted
        $this->booted(function () {
            $this->registerRoutes();
        });
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

    public function provides(): array
    {
        return [];
    }

    protected function registerConfig(): void
    {
        //
    }
}
