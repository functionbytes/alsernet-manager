<?php

namespace Modules\Supplier\Providers;

use App\Services\NavService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
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
        $this->registerConfig();
        $this->registerViews();
        $this->registerMenus();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        // Register services as singletons
        $this->app->singleton(
            \Modules\Supplier\Services\PromptSelectionService::class,
            fn ($app) => new \Modules\Supplier\Services\PromptSelectionService
        );

        $this->app->singleton(
            \Modules\Supplier\Services\ExtractionService::class,
            fn ($app) => new \Modules\Supplier\Services\ExtractionService
        );

        $this->app->singleton(
            \Modules\Supplier\Services\AutomationOrchestrationService::class,
            fn ($app) => new \Modules\Supplier\Services\AutomationOrchestrationService
        );

        $this->app->singleton(
            \Modules\Supplier\Services\ContentGenerationService::class,
            fn ($app) => new \Modules\Supplier\Services\ContentGenerationService
        );

        $this->app->singleton(
            \Modules\Supplier\Services\SourceConfigurationService::class,
            fn ($app) => new \Modules\Supplier\Services\SourceConfigurationService
        );
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // Register artisan commands here
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
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
     * Register navigation menus
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para Proveedores
        NavService::registerMiniItem('suppliers', [
            'icon' => 'fa-truck',
            'tooltip' => 'Proveedores',
            'sidebar_id' => 'suppliers',
            'order' => 50,
        ]);

        // Sidebar con los items del módulo
        NavService::registerSidebar('suppliers', [
            'title' => 'Proveedores',
            'items' => [
                ['label' => 'Gestión de proveedores', 'route' => 'settings.suppliers.index', 'icon' => 'fa-list'],
                ['label' => 'Prompts', 'route' => 'settings.suppliers.prompts.index', 'icon' => 'fa-comments'],
                ['label' => 'Templates', 'route' => 'settings.suppliers.templates.index', 'icon' => 'fa-file-code'],
                ['label' => 'Automatización', 'route' => 'settings.suppliers.automation.index', 'icon' => 'fa-robot'],
                ['label' => 'Contenido generado', 'route' => 'settings.suppliers.content.index', 'icon' => 'fa-file-alt'],
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
