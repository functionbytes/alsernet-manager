<?php

namespace Modules\Webhook\Providers;

use App\Services\NavService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Webhook\Models\WebhookIntegration;
use Modules\Webhook\Policies\SettingsPolicy;
use Modules\Webhook\Policies\WebhookPolicy;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class WebhookServiceProvider extends ServiceProvider
{
    protected string $name = 'Webhook';

    protected string $nameLower = 'webhook';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerMenus();
        $this->registerGates();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        // Register routes directly (Laravel 12 compatible)
        $this->registerRoutes();
    }

    public function register(): void
    {
        // Register policies
        $this->registerPolicies();
    }

    /**
     * Register policies
     */
    protected function registerPolicies(): void
    {
        if (class_exists(WebhookIntegration::class)) {
            Gate::policy(
                WebhookIntegration::class,
                WebhookPolicy::class
            );
        }
    }

    /**
     * Register gates for settings authorization
     */
    protected function registerGates(): void
    {
        $settingsPolicy = new SettingsPolicy;

        // Configure settings gates
        Gate::define('configure-webhooks', fn ($user) => $settingsPolicy->configure($user));
        Gate::define('view-webhook-settings', fn ($user) => $settingsPolicy->viewSettings($user));
        Gate::define('manage-webhook-integrations', fn ($user) => $settingsPolicy->manageIntegrations($user));
        Gate::define('manage-webhook-subscriptions', fn ($user) => $settingsPolicy->manageSubscriptions($user));
        Gate::define('manage-webhook-events', fn ($user) => $settingsPolicy->manageEvents($user));
        Gate::define('test-webhooks', fn ($user) => $settingsPolicy->testWebhooks($user));
    }

    /**
     * Register module routes
     */
    protected function registerRoutes(): void
    {
        // Webhook operational routes (day-to-day webhook monitoring)
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('webhooks')
            ->name('manager.webhooks.')
            ->group(function () {
                require module_path($this->name, 'routes/web.php');
            });

        // Webhook configuration routes (system settings)
        Route::middleware(['web', 'auth', 'role:manager|super-admin'])
            ->prefix('settings/webhooks')
            ->name('manager.settings.webhooks.')
            ->group(function () {
                require module_path($this->name, 'routes/settings.php');
            });

        // Public API routes
        Route::middleware(['api'])
            ->prefix('api/webhooks')
            ->name('api.webhooks.')
            ->group(function () {
                require module_path($this->name, 'routes/api.php');
            });
    }

    /**
     * Register module menus
     */
    protected function registerMenus(): void
    {
        // Mini-nav item for Webhooks
        NavService::registerMiniItem('webhooks', [
            'icon' => 'fa-webhook',
            'tooltip' => 'Webhooks',
            'sidebar_id' => 'webhooks',
            'order' => 40,
        ]);

        // Sidebar with module items
        NavService::registerSidebar('webhooks', [
            'title' => 'Webhooks',
            'items' => [
                ['label' => 'Listado de webhooks', 'route' => 'manager.webhooks.index'],
                ['label' => 'Entregas', 'route' => 'manager.webhooks.deliveries'],
                ['label' => 'Eventos', 'route' => 'manager.webhooks.events'],
                ['label' => 'Integraciones', 'route' => 'manager.settings.webhooks.integrations.index'],
                ['label' => 'Suscripciones', 'route' => 'manager.settings.webhooks.subscriptions.index'],
            ],
        ]);
    }

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

    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        if (is_array($module_config)) {
            config([$key => array_replace_recursive($existing, $module_config)]);
        }
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\'.$this->name.'\\View\\Components', $this->nameLower);
    }

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
