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
        Schema::create('document_storage_config_histories', function (Blueprint $table) {
            $table->id();
            $table->string('old_disk_name')->nullable();
            $table->string('new_disk_name')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('driver')->nullable();
            $table->text('reason')->nullable();
            $table->enum('action', ['create', 'update', 'delete'])->default('create');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('changed_by');
            $table->index('old_disk_name');
            $table->index('new_disk_name');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_storage_config_histories');
    }
};
