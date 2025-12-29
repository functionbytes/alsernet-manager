<?php

namespace Modules\Event\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path('Event', 'database/migrations'));
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path('Event', 'config/event.php') => config_path('event.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path('Event', 'config/event.php'), 'event'
        );
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/event');
        $sourcePath = module_path('Event', 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', 'event-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), 'event');
    }

    protected function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach ($this->app['config']->get('view.paths') as $path) {
            if (is_dir($path . '/modules/event')) {
                $paths[] = $path . '/modules/event';
            }
        }
        return $paths;
    }
}
