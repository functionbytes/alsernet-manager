<?php

namespace Modules\Mail\Providers;

use Illuminate\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register Mail-related services
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/managers.php');

        $this->publishes([
            __DIR__ . '/../../config/mail.php' => config_path('mail.php'),
        ], 'mail-config');
    }
}
