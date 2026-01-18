<?php

namespace Modules\Supplier\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Supplier\Commands\CleanupSyncCacheCommand;
use Modules\Supplier\Events\SupplierErpProviderUpdated;
use Modules\Supplier\Events\SupplierProductPriceChanged;
use Modules\Supplier\Events\SupplierProductUpdated;
use Modules\Supplier\Listeners\SyncPriceToErpListener;
use Modules\Supplier\Listeners\SyncProductToErpListener;
use Modules\Supplier\Listeners\SyncProviderToErpListener;
use Modules\Supplier\Models\SupplierErpProvider;
use Modules\Supplier\Models\SupplierProduct;
use Modules\Supplier\Models\SupplierProductPrice;
use Modules\Supplier\Observers\SupplierErpProviderObserver;
use Modules\Supplier\Observers\SupplierProductObserver;
use Modules\Supplier\Observers\SupplierProductPriceObserver;
use Modules\Supplier\Services\AutomationOrchestrationService;
use Modules\Supplier\Services\ContentGenerationService;
use Modules\Supplier\Services\ErpSyncService;
use Modules\Supplier\Services\ExtractionService;
use Modules\Supplier\Services\PromptSelectionService;
use Modules\Supplier\Services\SourceConfigurationService;
use Modules\Theme\Services\NavService;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SupplierServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Supplier';

    protected string $nameLower = 'supplier';

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
        $this->registerObservers();
        $this->registerEventListeners();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        // Merge module config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/supplier.php',
            'supplier'
        );

        // Register services as singletons
        $this->app->singleton(
            PromptSelectionService::class,
            fn ($app) => new PromptSelectionService
        );

        $this->app->singleton(
            ExtractionService::class,
            fn ($app) => new ExtractionService
        );

        $this->app->singleton(
            AutomationOrchestrationService::class,
            fn ($app) => new AutomationOrchestrationService
        );

        $this->app->singleton(
            ContentGenerationService::class,
            fn ($app) => new ContentGenerationService
        );

        $this->app->singleton(
            SourceConfigurationService::class,
            fn ($app) => new SourceConfigurationService
        );

        // Registrar servicio de sincronización inversa (Supplier → ERP)
        $this->app->singleton(
            ErpSyncService::class,
            fn ($app) => new ErpSyncService
        );
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            CleanupSyncCacheCommand::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Cleanup stale sync cache flags every hour
            $schedule->command('supplier:cleanup-sync-cache')
                ->hourly()
                ->withoutOverlapping()
                ->runInBackground();
        });
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
     * Register view composers for theme integration.
     */
    protected function registerViewComposers(): void
    {
        // Compositors específicos del módulo Supplier pueden ser agregados aquí si es necesario
    }

    /**
     * Register navigation menus
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para Proveedores
        NavService::registerMiniItem('suppliers', [
            'icon' => 'fa-duotone fa-truck',
            'tooltip' => 'Proveedores',
            'sidebar_id' => 'suppliers',
            'order' => 50,
        ]);

        // Sidebar operational - Gestión de suppliers
        NavService::registerSidebar('suppliers', [
            'title' => 'Proveedores',
            'items' => [
                ['label' => 'Gestión de proveedores', 'route' => 'suppliers.index'],
            ],
        ]);

        // Sidebar settings - Configuración del módulo Supplier
        NavService::registerSidebar('settings', [
            'title' => 'Proveedores',
            'items' => [
                ['label' => 'Crear proveedor', 'route' => 'settings.suppliers.create'],
                ['label' => 'Prompts', 'route' => 'settings.suppliers.prompts.index'],
                ['label' => 'Templates', 'route' => 'settings.suppliers.templates.index'],
                ['label' => 'Automatización', 'route' => 'settings.suppliers.automation.index'],
                ['label' => 'Contenido generado', 'route' => 'settings.suppliers.content.index'],
                ['label' => 'Sincronización', 'route' => 'settings.suppliers.sync.index'],
                ['label' => 'Fallos de sincronización', 'route' => 'settings.suppliers.sync-failures.index'],
            ],
        ]);
    }

    /**
     * Registrar Observers para detectar cambios en modelos
     *
     * Los observers disparan eventos cuando los modelos son modificados,
     * lo que inicia la sincronización bidireccional hacia Oracle.
     */
    protected function registerObservers(): void
    {
        SupplierProductPrice::observe(
            SupplierProductPriceObserver::class
        );

        SupplierErpProvider::observe(
            SupplierErpProviderObserver::class
        );

        SupplierProduct::observe(
            SupplierProductObserver::class
        );
    }

    /**
     * Registrar Event Listeners para sincronización bidireccional
     *
     * Los listeners reciben eventos y encolan jobs para sincronizar
     * cambios hacia Oracle en tiempo real.
     */
    protected function registerEventListeners(): void
    {
        // Registrar listeners para eventos de cambios en Supplier
        $this->app['events']->listen(
            SupplierProductPriceChanged::class,
            SyncPriceToErpListener::class
        );

        $this->app['events']->listen(
            SupplierErpProviderUpdated::class,
            SyncProviderToErpListener::class
        );

        $this->app['events']->listen(
            SupplierProductUpdated::class,
            SyncProductToErpListener::class
        );
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
