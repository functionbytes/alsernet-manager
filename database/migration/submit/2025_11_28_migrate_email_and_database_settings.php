<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert email/SMTP and database settings into the settings table
        $settings = [
            // Email/SMTP Settings
            [
                'key' => 'mail_mailer',
                'value' => env('MAIL_MAILER', 'smtp'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mail_host',
                'value' => env('MAIL_HOST', 'smtp.mailtrap.io'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mail_port',
                'value' => env('MAIL_PORT', '2525'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mail_username',
                'value' => env('MAIL_USERNAME', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mail_password',
                'value' => env('MAIL_PASSWORD', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mail_encryption',
                'value' => env('MAIL_ENCRYPTION', 'tls'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mail_from_address',
                'value' => env('MAIL_FROM_ADDRESS', 'mail@example.com'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mail_from_name',
                'value' => env('MAIL_FROM_NAME', env('APP_NAME', 'Alsernet')),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Database Settings
            [
                'key' => 'db_connection',
                'value' => env('DB_CONNECTION', 'mysql'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'db_host',
                'value' => env('DB_HOST', 'localhost'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'db_port',
                'value' => env('DB_PORT', '3306'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'db_database',
                'value' => env('DB_DATABASE', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'db_username',
                'value' => env('DB_USERNAME', 'root'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'db_password',
                'value' => env('DB_PASSWORD', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'db_charset',
                'value' => 'utf8mb4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'db_collation',
                'value' => 'utf8mb4_unicode_ci',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the email and database settings from the settings table
        $keys = [
            'mail_mailer',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
            'db_connection',
            'db_host',
            'db_port',
            'db_database',
            'db_username',
            'db_password',
            'db_charset',
            'db_collation',
        ];

        DB::table('settings')->whereIn('key', $keys)->delete();
    }
};
