<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('app.settings', fn () => $this->getSettings());
    }

    public function boot(): void
    {
        // Register organized migration paths
        $migrator = $this->app->make(Migrator::class);

        $migrator->path(database_path('migrations/documents'));
        $migrator->path(database_path('migrations/returns'));
        $migrator->path(database_path('migrations/helpdesk'));
        $migrator->path(database_path('migrations/mail'));
        $migrator->path(database_path('migrations/webhooks'));
        $migrator->path(database_path('migrations/inventaries'));
        $migrator->path(database_path('migrations/auth'));
        $migrator->path(database_path('migrations/core'));

        // TODO: Enable when Return module is enabled
        // ReturnRequest::observe(ReturnObserver::class);

        Schema::defaultStringLength(191);

        $this->changeDefaultSettings();

        $settings = $this->getSettings();
        if ($settings) {
            config([
                'websockets.dashboard.port' => $settings->liveChatPort ?? env('LIVE_CHAT_PORT', 6001),
                'broadcasting.connections.pusher.options.port' => $settings->liveChatPort ?? env('LIVE_CHAT_PORT', 6001),
                'broadcasting.connections.pusher.options.host' => parse_url(url('/'))['host'] ?? env('PUSHER_HOST', 'localhost'),
            ]);
        }

        // Hook system available when Campaign module is enabled
        if (class_exists(\Modules\Campaign\Library\Facades\Hook::class)) {
            foreach (\Modules\Campaign\Library\Facades\Hook::execute('add_translation_file') as $source) {
                if (! empty($source['translation_prefix']) && ! empty($source['translation_folder'])) {
                    $this->loadTranslationsFrom($source['translation_folder'], $source['translation_prefix']);
                }
            }
        }

        Queue::before(fn (JobProcessing $event) => $this->handleQueueEvent($event, 'before'));
        Queue::after(fn (JobProcessed $event) => $this->handleQueueEvent($event, 'after'));
        Queue::failing(fn (JobFailed $event) => $this->handleQueueEvent($event, 'failed'));

    }

    private function getSettings(): ?Setting
    {
        try {
            return cache()->remember('app_settings', now()->addMinutes(10), fn () => Setting::first());
        } catch (\Exception $e) {
            // Database not ready yet, return null gracefully
            return null;
        }
    }

    private function changeDefaultSettings(): void
    {
        ini_set('memory_limit', '-1');
        ini_set('pcre.backtrack_limit', '1000000000');

        if (request()->isSecure() || (! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0)) {
            URL::forceScheme('https');
        }
    }

    private function handleQueueEvent($event, string $type): void {}
}
