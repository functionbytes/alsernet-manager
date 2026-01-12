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
        Schema::create('price_validation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_validation_id')->constrained('price_validations')->onDelete('cascade');

            // Cambios de estado
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);

            // Cambios de precio
            $table->decimal('old_price', 10, 2)->nullable();
            $table->decimal('new_price', 10, 2)->nullable();
            $table->decimal('adjusted_price', 10, 2)->nullable();

            // Acción realizada
            $table->enum('action', [
                'created',              // Validación creada
                'status_changed',       // Cambio de estado
                'price_adjusted',       // Precio ajustado manualmente
                'approved',             // Aprobado
                'rejected',             // Rechazado
                'applied',              // Aplicado a Prestashop
                'reverted',             // Revertido
                'note_added',           // Nota agregada
                'batch_processed'       // Procesado en lote
            ]);

            // Auditoría
            $table->string('changed_by', 100)->nullable()->comment('Usuario que realizó el cambio');
            $table->string('ip_address', 45)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Datos adicionales en JSON');

            $table->timestamps();

            // Índices
            $table->index('price_validation_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_validation_history');
    }
};
