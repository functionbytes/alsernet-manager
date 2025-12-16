<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BootMailConfigurationProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            // Load mail configuration from database
            $mailConfig = config('mail.mailers.smtp');

            $mailConfig['host'] = \App\Models\Setting::where('key', 'mail.host')->value('value') ?? $mailConfig['host'];
            $mailConfig['port'] = \App\Models\Setting::where('key', 'mail.port')->value('value') ?? $mailConfig['port'];
            $mailConfig['username'] = \App\Models\Setting::where('key', 'mail.username')->value('value');
            $mailConfig['password'] = \App\Models\Setting::where('key', 'mail.password')->value('value');
            $mailConfig['encryption'] = \App\Models\Setting::where('key', 'mail.encryption')->value('value');

            config([
                'mail.mailers.smtp' => $mailConfig,
                'mail.from.address' => \App\Models\Setting::where('key', 'mail.from_address')->value('value') ?? config('mail.from.address'),
                'mail.from.name' => \App\Models\Setting::where('key', 'mail.from_name')->value('value') ?? config('mail.from.name'),
            ]);
        } catch (\Exception $e) {
            // If database is not ready, use default config
            \Log::warning('Could not load mail configuration from database: '.$e->getMessage());
        }
    }
}
