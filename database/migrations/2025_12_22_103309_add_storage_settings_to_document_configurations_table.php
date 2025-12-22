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
        Schema::table('document_configurations', function (Blueprint $table) {
            // Storage disk name (local, ftp, sftp, network, s3, etc.)
            $table->string('storage_disk')->nullable()->after('required_documents');

            // Storage path within the disk (relative path)
            $table->string('storage_path')->nullable()->after('storage_disk');

            // Storage configuration (JSON for disk-specific settings)
            $table->json('storage_config')->nullable()->after('storage_path');

            // Whether custom storage is enabled for this configuration
            $table->boolean('is_storage_enabled')->default(false)->after('storage_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_configurations', function (Blueprint $table) {
            $table->dropColumn([
                'storage_disk',
                'storage_path',
                'storage_config',
                'is_storage_enabled',
            ]);
        });
    }
};
