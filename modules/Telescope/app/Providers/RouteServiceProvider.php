<?php

namespace Modules\Telescope\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Telescope';

    /**
     * Called before routes are registered.
     */
    public function boot(): void
    {
        parent::boot();

        // Routes are handled by Laravel Telescope package itself
        // This provider is here for consistency with other modules
    }
}
