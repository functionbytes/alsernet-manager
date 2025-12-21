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
        Schema::create('supervisor_backups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del backup
            $table->string('description')->nullable(); // Descripción
            $table->enum('environment', ['dev', 'prod', 'staging'])->default('dev'); // Ambiente
            $table->json('config_files'); // Array de archivos de configuración
            $table->json('supervisor_status')->nullable(); // Estado de procesos al hacer backup
            $table->bigInteger('backup_size')->nullable(); // Tamaño del backup en bytes
            $table->timestamp('backed_up_at')->nullable(); // Cuándo se hizo el backup
            $table->timestamp('restored_at')->nullable(); // Cuándo se restauró
            $table->string('restored_by')->nullable(); // Quién lo restauró
            $table->boolean('is_auto')->default(false); // ¿Es un backup automático?
            $table->timestamps();

            $table->index('environment');
            $table->index('backed_up_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_backups');
    }
};
