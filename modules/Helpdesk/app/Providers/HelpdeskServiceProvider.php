<?php

namespace Modules\Helpdesk\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class HelpdeskServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Helpdesk';

    protected string $nameLower = 'helpdesk';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerViewComposers();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);

        // Register services as singletons
        $this->app->singleton(\Modules\Helpdesk\Services\TicketService::class);
        $this->app->singleton(\Modules\Helpdesk\Services\AssignmentService::class);
        $this->app->singleton(\Modules\Helpdesk\Services\SlaService::class);
        $this->app->singleton(\Modules\Helpdesk\Services\CannedReplyService::class);
        $this->app->singleton(\Modules\Helpdesk\Services\NotificationService::class);

        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        if (class_exists(\Modules\Helpdesk\Models\Ticket::class)) {
            \Illuminate\Support\Facades\Gate::policy(
                \Modules\Helpdesk\Models\Ticket::class,
                \Modules\Helpdesk\Policies\TicketPolicy::class
            );
        }
    }

    protected function registerCommands(): void
    {
        // Commands auto-discovered in app/Console/Commands
    }

    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

            // Email to ticket processing
            $schedule->command('imap:emailticket')->everyMinute();

            // Auto-close tickets
            $schedule->command('ticket:autoclose')->everyMinute();

            // Auto-overdue ticket detection
            $schedule->command('ticket:autooverdue')->everyMinute();

            // Auto-response for tickets
            $schedule->command('ticket:autoresponseticket')->everyMinute();

            // Clean up trashed tickets
            $schedule->command('trashedticket:autodelete')->everyMinute();

            // SLA breach checking
            $schedule->job(new \Modules\Helpdesk\Jobs\CheckSlaBreaches)
                ->everyFifteenMinutes()
                ->withoutOverlapping();

            // SLA warning notifications
            $schedule->job(new \Modules\Helpdesk\Jobs\SendSlaWarnings)
                ->everyThirtyMinutes();

            // Clean up old closed tickets
            $schedule->job(new \Modules\Helpdesk\Jobs\CleanupOldTickets)
                ->daily()
                ->at('02:00');
        });
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

    public function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

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
        view()->composer(
            'theme.components.nav',
            \Modules\Helpdesk\Http\ViewComposers\NavigationComposer::class
        );
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
