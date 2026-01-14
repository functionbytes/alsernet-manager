<?php

namespace Modules\Compliance\Providers;

use Illuminate\Support\ServiceProvider;

class ComplianceServiceProvider extends ServiceProvider
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
            __DIR__.'/../../config/compliance.php' => config_path('compliance.php'),
        ], 'config');
    }
}
