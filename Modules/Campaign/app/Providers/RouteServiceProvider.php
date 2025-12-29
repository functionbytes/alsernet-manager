<?php

namespace Modules\Campaign\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions that use the traditonal queue syntax.
     *
     * @var string
     */
    protected $namespace = 'Modules\Campaign\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapManagerRoutes();
    }

    /**
     * Define the manager routes for the application.
     */
    protected function mapManagerRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('manager')
            ->name('manager.')
            ->group(base_path('Modules/Campaign/routes/managers.php'));
    }
}
