<?php

namespace Modules\Helpdesk\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Helpdesk';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        // Helpdesk settings routes
        Route::middleware(['web', 'auth', 'role:super-admin'])
            ->prefix('settings/helpdesk')
            ->name('helpdesk.backups.')
            ->group(module_path($this->name, 'routes/web.php'));

        // Agent routes
        Route::middleware(['web', 'auth', 'role:helpdesk-agent|super-admin'])
            ->prefix('helpdesk')
            ->name('helpdesk.')
            ->group(module_path($this->name, 'routes/agents.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware(['api', 'throttle:60,1'])
            ->prefix('api/helpdesk')
            ->name('api.helpdesk.')
            ->group(module_path($this->name, 'routes/api.php'));
    }
}
