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
        Schema::create('scheduled_price_validations', function (Blueprint $table) {
            $table->id();

            // Referencia a Prestashop
            $table->unsignedBigInteger('id_specific_price')->comment('ID del specific_price de Prestashop');
            $table->unsignedBigInteger('id_product');
            $table->unsignedBigInteger('id_product_attribute')->default(0);
            $table->unsignedInteger('id_country')->default(0);
            $table->string('reference', 100)->nullable();

            // Fechas de vigencia (copiadas de specific_price)
            $table->timestamp('valid_from')->nullable()->comment('Fecha inicio de vigencia');
            $table->timestamp('valid_to')->nullable()->comment('Fecha fin de vigencia');

            // Control de ejecución
            $table->enum('status', [
                'scheduled',    // Programado, esperando fecha
                'processing',   // En proceso de ejecución
                'completed',    // Ejecutado exitosamente
                'failed',       // Falló la ejecución
                'cancelled'     // Cancelado
            ])->default('scheduled');

            // Resultados de ejecución
            $table->timestamp('executed_at')->nullable()->comment('Fecha de ejecución');
            $table->integer('executions_count')->default(0)->comment('Número de veces ejecutado');
            $table->timestamp('last_execution_at')->nullable();
            $table->timestamp('next_execution_at')->nullable()->comment('Próxima ejecución programada');

            // Resultados de la validación
            $table->decimal('gestion_price', 10, 2)->nullable()->comment('Precio obtenido de Gestión');
            $table->decimal('prestashop_price', 10, 2)->nullable()->comment('Precio calculado de Prestashop');
            $table->decimal('difference', 10, 2)->nullable();
            $table->boolean('has_difference')->default(false);

            // Relación con validación creada
            $table->foreignId('price_validation_id')->nullable()->constrained('price_validations')->onDelete('set null');

            // Log de errores
            $table->text('last_error')->nullable();
            $table->json('execution_log')->nullable()->comment('Log de ejecuciones en JSON');

            $table->timestamps();

            // Índices
            $table->index('id_specific_price');
            $table->index(['id_product', 'id_product_attribute', 'id_country'], 'idx_product_combination_country');
            $table->index('status');
            $table->index('valid_from');
            $table->index('valid_to');
            $table->index('next_execution_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_price_validations');
    }
};
