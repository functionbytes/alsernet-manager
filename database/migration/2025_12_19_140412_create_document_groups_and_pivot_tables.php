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
        // Create document_groups table
        Schema::create('document_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('assignment_mode', ['manual', 'round_robin', 'load_balanced'])->default('manual');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'order']);
            $table->index('is_default');
        });

        // Create pivot table for document_groups and users
        Schema::create('document_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_group_id')->constrained('document_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('priority', ['primary', 'backup'])->default('primary');
            $table->timestamp('created_at')->nullable();

            $table->unique(['document_group_id', 'user_id']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_group_user');
        Schema::dropIfExists('document_groups');
    }
};
