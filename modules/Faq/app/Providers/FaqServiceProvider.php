<?php

namespace Modules\Faq\Providers;

use Illuminate\Support\ServiceProvider;

class FaqServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register FAQ services
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/managers.php');

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'faq');

        $this->publishes([
            __DIR__.'/../../config/faq.php' => config_path('faq.php'),
        ], 'faq-config');
    }
}
