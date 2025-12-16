<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mapping de columnas de ErpSetting a keys de Settings
        $mapping = [
            'api_url' => 'erp_api_url',
            'sync_url' => 'erp_sync_url',
            'xmlrpc_url' => 'erp_xmlrpc_url',
            'sms_url' => 'erp_sms_url',
            'is_active' => 'erp_is_active',
            'timeout' => 'erp_timeout',
            'connect_timeout' => 'erp_connect_timeout',
            'retry_attempts' => 'erp_retry_attempts',
            'sync_destination_id' => 'erp_sync_destination_id',
            'sync_batch_size' => 'erp_sync_batch_size',
            'bizum_tpv_id' => 'erp_bizum_tpv_id',
            'google_tpv_id' => 'erp_google_tpv_id',
            'apple_tpv_id' => 'erp_apple_tpv_id',
            'enable_cache' => 'erp_enable_cache',
            'cache_ttl' => 'erp_cache_ttl',
            'enable_debug_logs' => 'erp_enable_debug_logs',
            'sms_username' => 'erp_sms_username',
            'sms_password' => 'erp_sms_password',
            'last_connection_check' => 'erp_last_connection_check',
            'last_connection_status' => 'erp_last_connection_status',
            'total_requests' => 'erp_total_requests',
            'failed_requests' => 'erp_failed_requests',
        ];

        // Obtener datos de erp_settings
        if (Schema::hasTable('erp_settings')) {
            $erpSettings = DB::table('erp_settings')->first();

            if ($erpSettings) {
                // Insertar datos en la tabla settings
                foreach ($mapping as $erpColumn => $settingKey) {
                    // Verificar que la propiedad existe
                    if (!property_exists($erpSettings, $erpColumn)) {
                        continue;
                    }

                    $value = $erpSettings->{$erpColumn};

                    // Convertir enteros (0/1) a string ('no'/'yes')
                    if (is_int($value) && in_array($erpColumn, ['is_active', 'enable_cache', 'enable_debug_logs'])) {
                        $value = $value ? 'yes' : 'no';
                    }

                    // Si el valor es null, no insertarlo
                    if ($value !== null) {
                        DB::table('settings')->updateOrInsert(
                            ['key' => $settingKey],
                            [
                                'key' => $settingKey,
                                'value' => (string) $value,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar las configuraciones ERP de la tabla settings
        $erpKeys = [
            'erp_api_url',
            'erp_sync_url',
            'erp_xmlrpc_url',
            'erp_sms_url',
            'erp_is_active',
            'erp_timeout',
            'erp_connect_timeout',
            'erp_retry_attempts',
            'erp_sync_destination_id',
            'erp_sync_batch_size',
            'erp_bizum_tpv_id',
            'erp_google_tpv_id',
            'erp_apple_tpv_id',
            'erp_enable_cache',
            'erp_cache_ttl',
            'erp_enable_debug_logs',
            'erp_sms_username',
            'erp_sms_password',
            'erp_last_connection_check',
            'erp_last_connection_status',
            'erp_total_requests',
            'erp_failed_requests',
            'erp_integration_enabled',
            'erp_import_documents',
            'erp_auto_detect_type',
            'erp_sync_products',
            'erp_sync_customers',
            'erp_document_source',
        ];

        DB::table('settings')->whereIn('key', $erpKeys)->delete();
    }
};