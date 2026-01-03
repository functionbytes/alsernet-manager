<?php

namespace Modules\Horizon\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Services\NavService;

class HorizonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->registerGates();
        $this->registerMenus();
    }

    private function loadRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(function () {
                require $this->module_path('routes/api.php');
            });

        Route::middleware('web')
            ->group(function () {
                require $this->module_path('routes/web.php');
            });
    }

    private function loadViews(): void
    {
        $this->loadViewsFrom(
            $this->module_path('resources/views'),
            'horizon'
        );
    }

    private function registerGates(): void
    {
        Gate::define('view-horizon', function ($user = null) {
            if (! $user) {
                return false;
            }

            return $user->hasAnyRole(['super-admin', 'manager']);
        });

        Gate::define('manage-horizon-settings', function ($user = null) {
            if (! $user) {
                return false;
            }

            return $user->hasRole('super-admin');
        });
    }

    private function registerMenus(): void
    {
        // Register mini-item in sidebar navigation
        NavService::registerMiniItem('horizon', [
            'icon' => 'fa-chart-area',
            'tooltip' => 'Colas y trabajos',
            'sidebar_id' => 'horizon',
            'order' => 85,
        ]);

        // Register sidebar group with submenu items
        NavService::registerSidebar('horizon', [
            'title' => 'Horizon - Monitoreo de colas',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'settings.horizon.index',
                    'icon' => 'fa-tachometer-alt',
                ],
                [
                    'label' => 'Trabajos activos',
                    'route' => 'settings.horizon.jobs',
                    'icon' => 'fa-list',
                ],
                [
                    'label' => 'Trabajos fallidos',
                    'route' => 'settings.horizon.failed',
                    'icon' => 'fa-exclamation-triangle',
                ],
                [
                    'label' => 'Métricas',
                    'route' => 'settings.horizon.metrics',
                    'icon' => 'fa-chart-line',
                ],
            ],
        ]);
    }

    private function module_path(string $path = ''): string
    {
        return __DIR__ . '/../../' . $path;
    }
}
