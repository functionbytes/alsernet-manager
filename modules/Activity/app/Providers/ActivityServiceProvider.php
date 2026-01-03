<?php

namespace Modules\Activity\Providers;

use Illuminate\Support\ServiceProvider;

class ActivityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerConfigurations();
    }

    protected function registerConfigurations(): void
    {
        $this->publishes([
            __DIR__.'/../../config/activity.php' => config_path('activity.php'),
        ], 'config');
    }
}
