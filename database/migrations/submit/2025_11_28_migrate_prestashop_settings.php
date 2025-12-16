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
        // Insertar configuraciones por defecto de PrestaShop en la tabla settings
        $prestashopSettings = [
            [
                'key' => 'prestashop_enabled',
                'value' => env('PRESTASHOP_ENABLED', 'no'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_db_host',
                'value' => env('DB_HOST_PRESTASHOP', '192.168.1.120'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_db_port',
                'value' => env('DB_PORT_PRESTASHOP', '3306'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_db_database',
                'value' => env('DB_DATABASE_PRESTASHOP', 'prestashop'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_db_username',
                'value' => env('DB_USERNAME_PRESTASHOP', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_db_password',
                'value' => env('DB_PASSWORD_PRESTASHOP', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_url',
                'value' => env('PRESTASHOP_URL', 'https://www.a-alvarez.com'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_api_key',
                'value' => env('PRESTASHOP_API_KEY', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_timeout',
                'value' => '30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_connect_timeout',
                'value' => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_sync_enabled',
                'value' => 'no',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_sync_products',
                'value' => 'yes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_sync_orders',
                'value' => 'yes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_sync_customers',
                'value' => 'yes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_documents_portal_url',
                'value' => env('DOCUMENTS_UPLOAD_PORTAL_URL', 'https://www.a-alvarez.com/solicitud-documentos?token={uid}'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_documents_paid_status_ids',
                'value' => env('DOCUMENTS_PRESTASHOP_PAID_STATUS_IDS', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_total_syncs',
                'value' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'prestashop_failed_syncs',
                'value' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($prestashopSettings as $setting) {
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
        // Eliminar las configuraciones PrestaShop de la tabla settings
        $prestashopKeys = [
            'prestashop_enabled',
            'prestashop_db_host',
            'prestashop_db_port',
            'prestashop_db_database',
            'prestashop_db_username',
            'prestashop_db_password',
            'prestashop_url',
            'prestashop_api_key',
            'prestashop_timeout',
            'prestashop_connect_timeout',
            'prestashop_sync_enabled',
            'prestashop_sync_products',
            'prestashop_sync_orders',
            'prestashop_sync_customers',
            'prestashop_documents_portal_url',
            'prestashop_documents_paid_status_ids',
            'prestashop_total_syncs',
            'prestashop_failed_syncs',
        ];

        DB::table('settings')->whereIn('key', $prestashopKeys)->delete();
    }
};
