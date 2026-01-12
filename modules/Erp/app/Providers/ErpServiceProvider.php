<?php

namespace Modules\Erp\Providers;

use Illuminate\Support\ServiceProvider;

class ErpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\Modules\Erp\Services\Integrations\ErpService::class, function ($app) {
            return new \Modules\Erp\Services\Integrations\ErpService;
        });

        $this->app->singleton(\Modules\Erp\Services\Integrations\GestionPriceService::class, function ($app) {
            return new \Modules\Erp\Services\Integrations\GestionPriceService;
        });

        // Circuit Breaker for Oracle connection resilience
        $this->app->singleton(\Modules\Erp\Services\CircuitBreaker::class, function ($app) {
            return new \Modules\Erp\Services\CircuitBreaker;
        });

        $this->app->alias('erp', \Modules\Erp\Services\Integrations\ErpService::class);
        $this->mergeConfigFrom(__DIR__.'/../../config/erp.php', 'erp');
        $this->mergeConfigFrom(__DIR__.'/../../config/database.php', 'database');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/erp.php' => config_path('erp.php'),
            __DIR__.'/../../config/database.php' => config_path('database-oracle.php'),
        ], 'erp-config');

        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        // Skip V2 migrations during testing to avoid conflicts with Core module migrations
        if (! app()->environment('testing')) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations/V2');
        }

        $this->commands([
            \Modules\Erp\Console\Commands\ErpCheckCommand::class,
            \Modules\Erp\Console\Commands\ClearProductImports::class,
            \Modules\Erp\Console\Commands\ExtractOracleDDL::class,
            \Modules\Erp\Console\Commands\ImportProductsFromPrestashop::class,
            \Modules\Erp\Console\Commands\ShowImportStatistics::class,
            \Modules\Erp\Console\Commands\SyncProducts::class,
            \Modules\Erp\Console\Commands\SyncSpecificPrices::class,
            \Modules\Erp\Console\Commands\TestOracleConnection::class,
        ]);
    }

}
