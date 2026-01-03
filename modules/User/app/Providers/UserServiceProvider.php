<?php

namespace Modules\User\Providers;

use App\Services\NavService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    protected string $name = 'User';

    public function register()
    {
        // Register User services
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'user');

        $this->publishes([
            __DIR__.'/../../config/user.php' => config_path('user.php'),
        ], 'user-config');

        // Register navigation menus
        $this->registerMenus();

        // Register routes after all providers have booted
        $this->booted(function () {
            $this->registerRoutes();
        });
    }

    /**
     * Register routes for the User module
     */
    protected function registerRoutes(): void
    {
        $modulePath = dirname(__DIR__, 2);

        // Manager settings routes (GET views + POST/PUT/DELETE API)
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('settings')
            ->name('settings.')
            ->group(function () use ($modulePath) {
                // Load view routes (GET)
                require $modulePath.'/routes/web.php';

                // Load API routes (POST, PUT, DELETE) if exists
                if (file_exists($modulePath.'/routes/api.php')) {
                    require $modulePath.'/routes/api.php';
                }
            });
    }

    /**
     * Register navigation menus for the User module
     */
    protected function registerMenus(): void
    {
        // Mini-nav item for Users
        NavService::registerMiniItem('users', [
            'icon' => 'fa-users',
            'tooltip' => 'Usuarios',
            'sidebar_id' => 'users',
            'order' => 30,
        ]);

        // Sidebar with menu items
        NavService::registerSidebar('users', [
            'title' => 'Usuarios',
            'items' => [
                ['label' => 'Todos los usuarios', 'route' => 'settings.users.index'],
                ['label' => 'Crear usuario', 'route' => 'settings.users.create'],
            ],
        ]);
    }
}
