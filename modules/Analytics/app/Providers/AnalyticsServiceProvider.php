<?php

namespace Modules\Analytics\Providers;

use Modules\Analytics\Analytics;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Analytics singleton
        $this->app->singleton('analytics', function ($app) {
            $propertyId = setting('google_analytics_property_id', '');
            $credentials = setting('google_analytics_credentials', '');

            return new Analytics($propertyId, $credentials);
        });
    }

    /**
     * Boot services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'analytics');

        // Register facade alias
        Facade::aliasNamespace('Analytics', 'Modules\\Analytics\\Facades');
    }
}

