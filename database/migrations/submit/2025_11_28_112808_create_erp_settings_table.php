<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_settings', function (Blueprint $table) {
            $table->id();

            // URLs de configuración
            $table->string('api_url')->default('http://interges:8080/api-gestion');
            $table->string('sync_url')->default('http://223.1.1.18:9000/integracion');
            $table->string('xmlrpc_url')->default('http://192.168.1.6:8081');
            $table->string('sms_url')->default('http://213.134.40.126:8080');

            // Configuración de conexión
            $table->boolean('is_active')->default(true);
            $table->integer('timeout')->default(30);
            $table->integer('connect_timeout')->default(10);
            $table->integer('retry_attempts')->default(3);

            // Configuración de sincronización
            $table->integer('sync_destination_id')->default(1);
            $table->integer('sync_batch_size')->default(100);

            // Configuración de TPV
            $table->integer('bizum_tpv_id')->nullable();
            $table->integer('google_tpv_id')->nullable();
            $table->integer('apple_tpv_id')->nullable();

            // Configuración de cache y logs
            $table->boolean('enable_cache')->default(true);
            $table->integer('cache_ttl')->default(3600);
            $table->boolean('enable_debug_logs')->default(false);

            // Credenciales SMS (opcional)
            $table->string('sms_username')->nullable();
            $table->string('sms_password')->nullable();

            // Estadísticas
            $table->timestamp('last_connection_check')->nullable();
            $table->string('last_connection_status')->nullable();
            $table->integer('total_requests')->default(0);
            $table->integer('failed_requests')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_settings');
    }
};