<?php

namespace Modules\Webhook\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->routes();
    }

    /**
     * Define the routes for this module.
     */
    protected function routes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('webhooks')
            ->name('webhooks.')
            ->group(__DIR__.'/../../routes/managers.php');

        Route::middleware(['api'])
            ->prefix('api/webhooks')
            ->name('api.webhooks.')
            ->group(__DIR__.'/../../routes/api.php');
    }
}
