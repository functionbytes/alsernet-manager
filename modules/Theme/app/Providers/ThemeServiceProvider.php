<?php

namespace Modules\Theme\Providers;

use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    protected string $name = 'Theme';

    protected string $nameLower = 'theme';

    public function register(): void
    {
        // Merge module config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/custom.php',
            'custom'
        );

        $this->app->register(MenuServiceProvider::class);
    }

    public function boot(): void
    {
        //
    }
}
