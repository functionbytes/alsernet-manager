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
        Schema::create('price_validations', function (Blueprint $table) {
            $table->id();

            // Información del producto
            $table->unsignedBigInteger('id_product')->comment('ID del producto en Prestashop');
            $table->unsignedBigInteger('id_product_attribute')->default(0)->comment('ID de combinación en Prestashop (0 si no tiene)');
            $table->unsignedInteger('id_country')->comment('ID del país');
            $table->string('reference', 100)->nullable()->comment('Referencia del producto');
            $table->string('product_name', 255)->nullable()->comment('Nombre del producto');

            // Precios
            $table->decimal('old_price', 10, 2)->default(0)->comment('Precio anterior/web antigua');
            $table->decimal('new_price', 10, 2)->default(0)->comment('Precio calculado/Prestashop');
            $table->decimal('adjusted_price', 10, 2)->nullable()->comment('Precio ajustado manualmente');
            $table->decimal('difference', 10, 2)->default(0)->comment('Diferencia de precio');
            $table->decimal('difference_percentage', 10, 2)->default(0)->comment('Porcentaje de diferencia');

            // Estado y control
            $table->enum('status', [
                'pending',      // Pendiente de revisión
                'validated',    // Validado automáticamente
                'adjusted',     // Ajustado manualmente
                'approved',     // Aprobado
                'rejected',     // Rechazado
                'applied'       // Aplicado a Prestashop
            ])->default('pending');

            $table->enum('validation_type', ['automatic', 'manual'])->default('automatic');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->comment('Prioridad según % diferencia');

            // Stock y disponibilidad
            $table->integer('stock')->default(0)->comment('Stock disponible');
            $table->boolean('is_available')->default(true)->comment('Si está disponible para gestión');
            $table->boolean('is_second_hand')->default(false)->comment('Si es de segunda mano');

            // Auditoría
            $table->string('validated_by', 100)->nullable()->comment('Usuario que validó');
            $table->timestamp('validated_at')->nullable()->comment('Fecha de validación');
            $table->string('applied_by', 100)->nullable()->comment('Usuario que aplicó el cambio');
            $table->timestamp('applied_at')->nullable()->comment('Fecha de aplicación');
            $table->text('notes')->nullable()->comment('Notas adicionales');

            // Batch processing
            $table->string('batch_id', 50)->nullable()->comment('ID del lote de procesamiento');
            $table->timestamp('processed_at')->nullable()->comment('Fecha de procesamiento');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['id_product', 'id_product_attribute', 'id_country'], 'idx_product_country');
            $table->index('status');
            $table->index('batch_id');
            $table->index('reference');
            $table->index('validated_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_validations');
    }
};
