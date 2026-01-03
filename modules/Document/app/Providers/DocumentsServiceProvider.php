<?php

namespace Modules\Document\Providers;

use App\Services\NavService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Document\Commands\CreateSampleDocumentsFromPrestashop;
use Modules\Document\Commands\InitializeDocumentWorkflows;
use Modules\Document\Commands\SendDocumentUploadReminders;
use Modules\Document\Commands\SyncDocumentFields;
use Modules\Document\Entities\Document;
use Modules\Document\Http\ViewComposers\NavigationComposer;
use Modules\Document\Policies\DocumentPolicy;
use Modules\Document\Policies\SettingsPolicy;
use Modules\Document\Services\PermissionService;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DocumentsServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Document';

    protected string $nameLower = 'documents';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerViewComposers();
        $this->registerMenus();
        $this->registerGates();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        // Register routes directly (Laravel 12 compatible)
        $this->registerRoutes();
    }

    public function register(): void
    {
        $this->app->singleton(
            PermissionService::class,
            fn ($app) => new PermissionService
        );

        // Registrar DocumentPolicy
        $this->registerPolicies();
    }

    /**
     * Registrar políticas de autorización
     */
    protected function registerPolicies(): void
    {
        if (class_exists(Document::class)) {
            Gate::policy(
                Document::class,
                DocumentPolicy::class
            );
        }
    }

    /**
     * Registrar gates para autorización de settings
     */
    protected function registerGates(): void
    {
        $settingsPolicy = new SettingsPolicy;

        // Configure settings gate
        Gate::define('configure-documents', fn ($user) => $settingsPolicy->configure($user));
        Gate::define('view-document-settings', fn ($user) => $settingsPolicy->viewSettings($user));
        Gate::define('manage-document-types', fn ($user) => $settingsPolicy->manageTypes($user));
        Gate::define('manage-document-conditions', fn ($user) => $settingsPolicy->manageConditions($user));
        Gate::define('manage-document-sla-policies', fn ($user) => $settingsPolicy->manageSLAPolicies($user));
        Gate::define('manage-document-groups', fn ($user) => $settingsPolicy->manageGroups($user));
        Gate::define('manage-document-blockades', fn ($user) => $settingsPolicy->manageBlockades($user));
    }

    /**
     * Register module routes
     */
    protected function registerRoutes(): void
    {
        // Web routes (operational and configuration) - middleware/prefix/name applied within the file
        require module_path($this->name, 'routes/web.php');

        // Public API routes
        Route::middleware(['api', 'throttle:60,1'])
            ->prefix('api/documents')
            ->name('api.documents.')
            ->group(function () {
                require module_path($this->name, 'routes/api.php');
            });
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            SendDocumentUploadReminders::class,
            InitializeDocumentWorkflows::class,
            CreateSampleDocumentsFromPrestashop::class,
            SyncDocumentFields::class,
        ]);
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

    protected function registerViewComposers(): void
    {
        // Register navigation composer for theme layout
        view()->composer(
            'theme.components.nav',
            NavigationComposer::class
        );
    }

    /**
     * Registrar menús del módulo Document
     */
    protected function registerMenus(): void
    {
        // Mini-nav item para Documentos
        NavService::registerMiniItem('documents', [
            'icon' => 'fa-file-pdf',
            'tooltip' => 'Documentos',
            'sidebar_id' => 'documents',
            'order' => 20,
        ]);

        // Sidebar local - Documentos (operaciones + configuración)
        NavService::registerSidebar('documents', [
            'title' => 'Documentos',
            'items' => [
                ['label' => 'Listado de documentos', 'route' => 'documents.index'],
                ['label' => 'Configuración global', 'route' => 'settings.documents.configurations.global'],
                ['label' => 'Tipos de documento', 'route' => 'settings.documents.types.index'],
                ['label' => 'Condiciones de validación', 'route' => 'settings.documents.conditions.index'],
                ['label' => 'Políticas SLA', 'route' => 'settings.documents.sla-policies.index'],
                ['label' => 'Grupos de validadores', 'route' => 'settings.documents.groups.index'],
                ['label' => 'Bloqueos de productos', 'route' => 'settings.documents.blockades.index'],
                ['label' => 'Acciones de email por etapa', 'route' => 'settings.documents.stage-email-actions.index'],
            ],
        ]);

        // Sidebar global - Settings (también registra configuraciones de documentos)
        NavService::registerSidebar('settings', [
            'title' => 'Documentos',
            'items' => [
                ['label' => 'Configuración global', 'route' => 'settings.documents.configurations.global'],
                ['label' => 'Tipos de documento', 'route' => 'settings.documents.types.index'],
                ['label' => 'Condiciones de validación', 'route' => 'settings.documents.conditions.index'],
                ['label' => 'Políticas SLA', 'route' => 'settings.documents.sla-policies.index'],
                ['label' => 'Grupos de validadores', 'route' => 'settings.documents.groups.index'],
                ['label' => 'Bloqueos de productos', 'route' => 'settings.documents.blockades.index'],
                ['label' => 'Acciones de email por etapa', 'route' => 'settings.documents.stage-email-actions.index'],
            ],
        ]);
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
