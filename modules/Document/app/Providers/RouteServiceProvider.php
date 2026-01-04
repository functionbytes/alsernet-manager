<?php

namespace Modules\Document\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Document';

    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        // Web routes (operational and configuration)
        // Middleware, prefix, and name are applied within the route file
        require module_path($this->name, 'routes/web.php');
    }

    protected function mapApiRoutes(): void
    {
        // Register Document API routes with proper prefix and naming
        Route::prefix('api/documents')
            ->middleware('api')
            ->name('api.documents.')
            ->group(function () {
                require module_path($this->name, 'routes/api.php');
            });
    }
}
