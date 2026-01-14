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
        Schema::create('product_imports', function (Blueprint $table) {
            $table->id();

            // Polimorfismo - permite referenciar tanto productos como combinaciones
            $table->morphs('importable'); // Crea importable_type y importable_id

            // Datos de Gestión
            $table->unsignedBigInteger('id_origen')->nullable();
            $table->unsignedBigInteger('id_articulo')->nullable();
            $table->integer('unidades_oferta')->default(0);
            $table->text('etiqueta')->nullable();

            // Estados
            $table->tinyInteger('estado_gestion')->default(0);
            $table->integer('activo')->default(0);
            $table->tinyInteger('es_segunda_mano')->default(0);

            // Información de proveedor
            $table->integer('externo_disponibilidad')->default(0);
            $table->string('codigo_proveedor', 128)->nullable();
            $table->decimal('precio_costo_proveedor', 10, 2)->default(0);
            $table->decimal('tarifa_proveedor', 10, 2)->default(0);

            // Clasificación de producto
            $table->integer('es_arma')->default(0);
            $table->integer('es_arma_fogueo')->default(0);
            $table->integer('es_cartucho')->default(0);

            // Códigos de barras
            $table->string('ean', 50)->nullable();
            $table->string('upc', 50)->nullable();

            // Categorización
            $table->integer('categoria')->default(0);
            $table->integer('familia')->default(0);
            $table->integer('subfamilia')->default(0);
            $table->integer('grupo')->default(0);

            // Control de sincronización
            $table->timestamp('last_sync_at')->nullable()->comment('Última sincronización con Prestashop');
            $table->boolean('sync_pending')->default(false)->comment('Pendiente de sincronizar');
            $table->json('sync_errors')->nullable()->comment('Errores de sincronización');

            $table->timestamps();

            // Índices (morphs() ya crea índice para importable_type e importable_id)
            $table->index('id_articulo');
            $table->index('estado_gestion');
            $table->index('es_segunda_mano');
            $table->index('categoria');
            $table->index('sync_pending');
            $table->unique(['importable_type', 'importable_id'], 'unique_importable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_imports');
    }
};
