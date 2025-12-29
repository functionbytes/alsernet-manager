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
            $table->string('old_disk_name')->nullable()->comment('Previous disk name');
            $table->string('new_disk_name')->comment('New disk name');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete()->comment('User who made the change');
            $table->string('driver')->comment('Storage driver type (local, ftp, sftp, s3, etc)');
            $table->text('reason')->nullable()->comment('Reason for the change');
            $table->string('action')->default('update')->comment('Action type: create, update, delete');
            $table->json('metadata')->nullable()->comment('Additional metadata about the change');
            $table->timestamps();

            // Indices for better query performance
            $table->index(['changed_by', 'created_at']);
            $table->index('new_disk_name');
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
