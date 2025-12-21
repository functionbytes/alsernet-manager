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
        Schema::create('document_syncs', function (Blueprint $table) {
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
        DB::table('document_syncs')->insert([
            ['key' => 'manual', 'label' => 'Manual', 'description' => 'Sincronización manual desde el panel', 'icon' => 'fas fa-hand-pointer', 'color' => 'secondary', 'is_active' => true, 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'automatic', 'label' => 'Automático', 'description' => 'Sincronización automática vía API', 'icon' => 'fas fa-sync-alt', 'color' => 'success', 'is_active' => true, 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_syncs');
    }
};
