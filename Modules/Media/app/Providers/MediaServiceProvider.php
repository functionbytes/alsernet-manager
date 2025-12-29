<?php

namespace Modules\Media\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge module config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/media.php',
            'media'
        );
    }

    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/managers.php');

        // Publish config
        $this->publishes([
            __DIR__ . '/../../config/media.php' => config_path('media.php'),
        ], 'media-config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'media');

        // Register policies
        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(
            \Modules\Media\Entities\MediaFile::class,
            \Modules\Media\Policies\MediaFilePolicy::class
        );

        Gate::policy(
            \Modules\Media\Entities\MediaFolder::class,
            \Modules\Media\Policies\MediaFolderPolicy::class
        );
    }
}
