<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class DynamicConfigServiceProvider extends ServiceProvider
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
        // Only load if database is available
        if (! $this->app->runningInConsole() || $this->isDatabaseReady()) {
            try {
                // Load database configuration
                $this->loadDatabaseConfig();

                // Load mail configuration
                $this->loadMailConfig();

                // Load prestashop configuration
                $this->loadPrestashopConfig();

                // Load custom storage disks
                $this->loadStorageConfig();
            } catch (\Exception $e) {
                // Silently fail if database is not ready
                // This allows the app to boot even if database is not configured
            }
        }
    }

    /**
     * Check if database is ready and configured
     */
    private function isDatabaseReady(): bool
    {
        try {
            // Try to connect to database
            \DB::connection()->getPDO();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Load database configuration from backups
     */
    private function loadDatabaseConfig(): void
    {
        try {
            $dbSettings = Setting::getDatabaseSettings();

            config([
                'database.default' => $dbSettings['db_connection'],
                'database.connections.mysql' => [
                    'driver' => 'mysql',
                    'host' => $dbSettings['db_host'],
                    'port' => (int) $dbSettings['db_port'],
                    'database' => $dbSettings['db_database'],
                    'username' => $dbSettings['db_username'],
                    'password' => $dbSettings['db_password'],
                    'unix_socket' => env('DB_SOCKET', ''),
                    'charset' => $dbSettings['db_charset'],
                    'collation' => $dbSettings['db_collation'],
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'strict' => true,
                    'engine' => null,
                    'options' => extension_loaded('pdo_mysql') ? array_filter([
                        \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                    ]) : [],
                ],
            ]);
        } catch (\Exception $e) {
            // Database config not available yet
        }
    }

    /**
     * Load mail configuration from backups
     */
    private function loadMailConfig(): void
    {
        try {
            $mailSettings = Setting::getEmailSettings();

            config([
                'mail.default' => $mailSettings['mail_mailer'],
                'mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'host' => $mailSettings['mail_host'],
                    'port' => (int) $mailSettings['mail_port'],
                    'encryption' => $mailSettings['mail_encryption'],
                    'username' => $mailSettings['mail_username'],
                    'password' => $mailSettings['mail_password'],
                    'timeout' => null,
                    'local_domain' => env('MAIL_LOCAL_DOMAIN'),
                ],
                'mail.from' => [
                    'address' => $mailSettings['mail_from_address'],
                    'name' => $mailSettings['mail_from_name'],
                ],
            ]);
        } catch (\Exception $e) {
            // Mail config not available yet
        }
    }

    /**
     * Load prestashop configuration from backups
     */
    private function loadPrestashopConfig(): void
    {
        try {
            $psSettings = Setting::getPrestashopSettings();

            config([
                'prestashop' => [
                    'enabled' => $psSettings['prestashop_enabled'] === 'yes',
                    'db_host' => $psSettings['prestashop_db_host'],
                    'db_port' => (int) $psSettings['prestashop_db_port'],
                    'db_database' => $psSettings['prestashop_db_database'],
                    'db_username' => $psSettings['prestashop_db_username'],
                    'db_password' => $psSettings['prestashop_db_password'],
                    'url' => $psSettings['prestashop_url'],
                    'api_key' => $psSettings['prestashop_api_key'],
                    'timeout' => (int) $psSettings['prestashop_timeout'],
                    'connect_timeout' => (int) $psSettings['prestashop_connect_timeout'],
                    'sync_enabled' => $psSettings['prestashop_sync_enabled'] === 'yes',
                    'sync_products' => $psSettings['prestashop_sync_products'] === 'yes',
                    'sync_orders' => $psSettings['prestashop_sync_orders'] === 'yes',
                    'sync_customers' => $psSettings['prestashop_sync_customers'] === 'yes',
                    'documents_portal_url' => $psSettings['prestashop_documents_portal_url'],
                    'documents_paid_status_ids' => $psSettings['prestashop_documents_paid_status_ids'],
                ],
            ]);
        } catch (\Exception $e) {
            // PrestaShop config not available yet
        }
    }

    /**
     * Load custom storage disks from backups
     */
    private function loadStorageConfig(): void
    {
        try {
            $customDisksJson = Setting::get('system.custom_storage_disks', '[]');
            $customDisks = json_decode($customDisksJson, true) ?: [];

            // Register each custom disk with Laravel's filesystem config
            foreach ($customDisks as $disk) {
                $diskName = $disk['name'];
                $driver = $disk['driver'];

                // Build disk configuration based on driver type
                $diskConfig = [
                    'driver' => $driver,
                ];

                // Add driver-specific configuration
                switch ($driver) {
                    case 'local':
                        $diskConfig['root'] = $disk['root'] ?? storage_path('app');
                        if (isset($disk['url'])) {
                            $diskConfig['url'] = $disk['url'];
                        }
                        $diskConfig['throw'] = false;
                        break;

                    case 'ftp':
                        $diskConfig['host'] = $disk['host'] ?? '';
                        $diskConfig['username'] = $disk['username'] ?? '';
                        $diskConfig['password'] = $disk['password'] ?? '';
                        $diskConfig['port'] = (int) ($disk['port'] ?? 21);
                        $diskConfig['root'] = $disk['root'] ?? '/';
                        $diskConfig['passive'] = true;
                        $diskConfig['ssl'] = false;
                        $diskConfig['timeout'] = 30;
                        break;

                    case 'sftp':
                        $diskConfig['host'] = $disk['host'] ?? '';
                        $diskConfig['username'] = $disk['username'] ?? '';
                        $diskConfig['password'] = $disk['password'] ?? '';
                        $diskConfig['port'] = (int) ($disk['port'] ?? 22);
                        $diskConfig['root'] = $disk['root'] ?? '/';
                        $diskConfig['timeout'] = 30;
                        break;

                    case 's3':
                        $diskConfig['key'] = $disk['key'] ?? '';
                        $diskConfig['secret'] = $disk['secret'] ?? '';
                        $diskConfig['region'] = $disk['region'] ?? '';
                        $diskConfig['bucket'] = $disk['bucket'] ?? '';
                        if (isset($disk['url'])) {
                            $diskConfig['url'] = $disk['url'];
                        }
                        $diskConfig['endpoint'] = $disk['endpoint'] ?? null;
                        $diskConfig['use_path_style_endpoint'] = false;
                        $diskConfig['throw'] = false;
                        break;
                }

                // Register the disk configuration
                config(["filesystems.disks.{$diskName}" => $diskConfig]);
            }
        } catch (\Exception $e) {
            // Storage config not available yet
        }
    }
}
