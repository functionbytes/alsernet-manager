<?php

namespace Modules\Campaign\Providers;

use Illuminate\Support\ServiceProvider;

class CampaignServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/campaign.php',
            'campaign'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishConfig();
    }

    /**
     * Publish the package configuration file.
     */
    private function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/campaign.php' => config_path('campaign.php'),
        ], 'campaign-config');
    }
}
