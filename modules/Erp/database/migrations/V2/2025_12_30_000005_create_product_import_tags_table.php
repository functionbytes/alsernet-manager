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
        // Tabla de etiquetas únicas
        Schema::create('product_import_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Nombre de la etiqueta (ej: NOGOO_ES, OUTLET)');
            $table->string('description')->nullable()->comment('Descripción de la etiqueta');
            $table->integer('products_count')->default(0)->comment('Contador de productos con esta etiqueta');
            $table->timestamps();

            $table->index('name');
        });

        // Tabla pivot para relacionar ProductImport con Tags
        Schema::create('product_import_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_import_id')->constrained('product_imports')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('product_import_tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_import_id', 'tag_id']);
            $table->index('product_import_id');
            $table->index('tag_id');
        });

        // Tabla para relacionar tags con features de Prestashop
        Schema::create('product_import_tag_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('product_import_tags')->onDelete('cascade');

            // Relación con Prestashop feature
            $table->unsignedBigInteger('id_feature')->comment('ID del feature en Prestashop');
            $table->unsignedBigInteger('id_feature_value')->nullable()->comment('ID del valor del feature en Prestashop');

            $table->timestamps();

            $table->unique(['tag_id', 'id_feature', 'id_feature_value'], 'unique_tag_feature');
            $table->index('tag_id');
            $table->index(['id_feature', 'id_feature_value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_import_tag_features');
        Schema::dropIfExists('product_import_tag');
        Schema::dropIfExists('product_import_tags');
    }
};
