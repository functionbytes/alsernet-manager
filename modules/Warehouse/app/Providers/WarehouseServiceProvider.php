<?php

namespace Modules\Warehouse\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Theme\Services\NavService;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class WarehouseServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Warehouse';

    protected string $nameLower = 'warehouse';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerViewComposers();
        $this->registerMenus();
        $this->loadMigrationsFrom(base_path('database/migrations/warehouses'));

        // Register routes directly (Laravel 12 compatible)
        $this->registerRoutes();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Registrar WarehouseLayoutParser como singleton
        $this->app->singleton(
            \Modules\Warehouse\Services\WarehouseLayoutParser::class,
            fn ($app) => new \Modules\Warehouse\Services\WarehouseLayoutParser
        );

        // Registrar policies
        $this->registerPolicies();
    }

    /**
     * Register module routes
     */
    protected function registerRoutes(): void
    {
        $modulePath = module_path($this->name);

        // Manager routes (super-admin only)
        \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'role:super-admin'])
            ->prefix('settings/warehouse')
            ->name('settings.warehouse.')
            ->group(function () use ($modulePath) {
                require $modulePath.'/routes/web.php';
            });

        // Warehouse worker routes (requires warehouse.access permission)
        \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'check.roles.permissions:warehouse'])
            ->prefix('warehouse')
            ->name('warehouse.')
            ->group(function () use ($modulePath) {
                require $modulePath.'/routes/warehouses.php';
            });

        // API routes
        \Illuminate\Support\Facades\Route::middleware(['api', 'throttle:60,1'])
            ->prefix('api/warehouse')
            ->name('api.warehouse.')
            ->group(function () use ($modulePath) {
                require $modulePath.'/routes/api.php';
            });
    }

    /**
     * Register policies for warehouse models
     */
    protected function registerPolicies(): void
    {
        if (class_exists(\Modules\Warehouse\Entities\Warehouse::class)) {
            Gate::policy(
                \Modules\Warehouse\Entities\Warehouse::class,
                \Modules\Warehouse\Policies\WarehousePolicy::class
            );
        }

        if (class_exists(\Modules\Warehouse\Entities\WarehouseFloor::class)) {
            Gate::policy(
                \Modules\Warehouse\Entities\WarehouseFloor::class,
                \Modules\Warehouse\Policies\WarehouseFloorPolicy::class
            );
        }

        if (class_exists(\Modules\Warehouse\Entities\WarehouseLocation::class)) {
            Gate::policy(
                \Modules\Warehouse\Entities\WarehouseLocation::class,
                \Modules\Warehouse\Policies\WarehouseLocationPolicy::class
            );
        }

        if (class_exists(\Modules\Warehouse\Entities\WarehouseInventorySlot::class)) {
            Gate::policy(
                \Modules\Warehouse\Entities\WarehouseInventorySlot::class,
                \Modules\Warehouse\Policies\WarehouseInventorySlotPolicy::class
            );
        }

        if (class_exists(\Modules\Warehouse\Entities\WarehouseInventoryOperation::class)) {
            Gate::policy(
                \Modules\Warehouse\Entities\WarehouseInventoryOperation::class,
                \Modules\Warehouse\Policies\WarehouseInventoryOperationPolicy::class
            );
        }

        if (class_exists(\Modules\Warehouse\Entities\WarehouseLocationStyle::class)) {
            Gate::policy(
                \Modules\Warehouse\Entities\WarehouseLocationStyle::class,
                \Modules\Warehouse\Policies\WarehouseLocationStylePolicy::class
            );
        }
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // Commands will be registered here as they are created
        // $this->commands([
        //     \modules\Warehouse\Commands\SyncWarehouseInventory::class,
        // ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('warehouse:sync')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        // Only merge if the config file returned an array
        if (is_array($module_config)) {
            config([$key => array_replace_recursive($existing, $module_config)]);
        }
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\'.$this->name.'\\View\\Components', $this->nameLower);
    }

    /**
     * Register view composers.
     */
    protected function registerViewComposers(): void
    {
        // Register navigation composer for theme layout
        view()->composer(
            'theme.components.nav',
            \Modules\Warehouse\Http\ViewComposers\NavigationComposer::class
        );
    }

    /**
     * Register navigation menus for the Warehouse module
     */
    protected function registerMenus(): void
    {
        // Mini-nav item for Warehouse
        NavService::registerMiniItem('warehouse', [
            'icon' => 'fa-warehouse',
            'tooltip' => 'Almacén',
            'sidebar_id' => 'warehouse',
            'order' => 40,
        ]);

        // Sidebar with menu items
        NavService::registerSidebar('warehouse', [
            'title' => 'Almacén',
            'items' => [
                ['label' => 'Almacenes', 'route' => 'settings.warehouse.index'],
                ['label' => 'Pisos', 'route' => 'settings.warehouse.floors'],
                ['label' => 'Ubicaciones', 'route' => 'settings.warehouse.stands'],
                ['label' => 'Estilos', 'route' => 'settings.warehouse.styles.index'],
                ['label' => 'Inventario', 'route' => 'settings.warehouse.slots'],
                ['label' => 'Mapa Visual', 'route' => 'settings.warehouse.map'],
            ],
        ]);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
