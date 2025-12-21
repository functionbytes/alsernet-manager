<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Elimina las tablas document_groups y document_group_user
     * ya que los datos fueron migrados a validator_groups y validator_group_user
     */
    public function up(): void
    {
        // Drop pivot table first (has foreign keys)
        Schema::dropIfExists('document_group_user');

        // Drop main table
        Schema::dropIfExists('document_groups');
    }

    /**
     * Reverse the migrations.
     *
     * Recrea las tablas si se necesita hacer rollback
     */
    public function down(): void
    {
        // Recreate document_groups
        Schema::create('document_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('key', 100)->unique();
            $table->text('description')->nullable();
            $table->enum('assignment_mode', ['manual', 'round_robin', 'load_balanced'])->default('manual');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        // Recreate pivot table
        Schema::create('document_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_group_id')->constrained('document_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('priority', ['primary', 'backup'])->default('primary');
            $table->timestamp('created_at')->nullable();

            $table->unique(['document_group_id', 'user_id']);
        });
    }
};
