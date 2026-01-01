<?php

namespace Modules\Erp\Providers;

use Modules\Erp\Services\Integrations\ErpService;
use Modules\Erp\Facades\Erp as ErpFacade;
use Illuminate\Support\ServiceProvider;

class ErpServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register ERP Service as singleton
        $this->app->singleton(ErpService::class, function ($app) {
            return new ErpService;
        });

        // Register Facade alias
        $this->app->alias('erp', ErpService::class);
    }

    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/erp.php' => config_path('erp.php'),
        ], 'erp-config');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/managers.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }
}
