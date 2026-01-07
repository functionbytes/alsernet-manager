<?php

namespace Modules\Document\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\Api\DocumentsController;

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
        // ✅ Cargar TODAS las rutas desde routes/api.php
        // El archivo api.php define los middlewares de cada grupo (throttle, auth, etc.)
        Route::prefix('api/documents')
            ->middleware('api')
            ->name('api.documents.')
            ->group(function () {
                require module_path($this->name, 'routes/api.php');
            });
    }
}
