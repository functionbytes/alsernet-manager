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
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->registerViewComposers();
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

    /**
     * Register view composers.
     */
    protected function registerViewComposers(): void
    {
        // Register navigation composer for managers layout
        view()->composer(
            'managers.includes.nav',
            \Modules\Campaign\Http\ViewComposers\NavigationComposer::class
        );
    }
}
