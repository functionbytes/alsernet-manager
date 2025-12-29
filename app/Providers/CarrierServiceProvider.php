<?php

namespace App\Providers;

use Modules\Returns\Services\CarrierService;
use Illuminate\Support\ServiceProvider;

class CarrierServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('carrier.service', function ($app) {
            return new CarrierService;
        });
    }

    public function boot()
    {
        // Publicar configuración
        $this->publishes([
            __DIR__.'/../../config/carriers.php' => config_path('carriers.php'),
        ], 'carriers-config');

        // Cargar migraciones
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations/carriers');
    }
}
