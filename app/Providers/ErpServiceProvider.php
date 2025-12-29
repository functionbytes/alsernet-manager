<?php

namespace App\Providers;

use Modules\Supplier\Services\Integrations\ErpService;
use Illuminate\Support\ServiceProvider;

class ErpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ErpService::class, function ($app) {
            return new ErpService;
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/erp.php' => config_path('erp.php'),
        ], 'config');
    }
}
