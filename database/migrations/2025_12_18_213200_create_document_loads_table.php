<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_loads', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Insert default values
        DB::table('document_loads')->insert([
            ['key' => 'manual', 'label' => 'Manual', 'description' => 'Carga manual', 'icon' => 'fas fa-hand-pointer', 'color' => 'secondary', 'is_active' => true, 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'email', 'label' => 'Email', 'description' => 'Importado desde email', 'icon' => 'fas fa-envelope', 'color' => 'primary', 'is_active' => true, 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'api', 'label' => 'API', 'description' => 'Cargado vía integración API', 'icon' => 'fas fa-code', 'color' => 'purple', 'is_active' => true, 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'erp', 'label' => 'Gestión ERP', 'description' => 'Importado desde Gestión ERP', 'icon' => 'fas fa-database', 'color' => 'info', 'is_active' => true, 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_loads');
    }
};
