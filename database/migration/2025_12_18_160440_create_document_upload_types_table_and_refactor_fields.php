<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates document_upload_types table and refactors documents table:
     * - Replaces string 'source' with 'source_id' foreign key to document_sources
     * - Replaces 'upload_type' enum with 'upload_id' foreign key to document_upload_types
     */
    public function up(): void
    {
        // Create document_upload_types table with automatic and manual options
        Schema::create('document_upload_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Identifier (automatic, manual)');
            $table->string('label')->comment('Display name');
            $table->string('description')->nullable()->comment('Description of the upload type');
            $table->string('icon')->nullable()->comment('Icon class');
            $table->string('color')->nullable()->comment('Color code');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->index('is_active');
        });

        // Seed default upload types
        Schema::table('document_upload_types', function (Blueprint $table) {
            // This is a placeholder - seeding will be done via seeder
        });

        // Refactor documents table
        Schema::table('documents', function (Blueprint $table) {
            // Drop old source column if it exists
            if (Schema::hasColumn('documents', 'source')) {
                $table->dropColumn('source');
            }

            // Drop old upload_type column if it exists (it was created as enum in previous migration)
            if (Schema::hasColumn('documents', 'upload_type')) {
                $table->dropColumn('upload_type');
            }

            // Add source_id foreign key to document_sources
            $table->unsignedBigInteger('source_id')->nullable()->after('proccess')->comment('Channel/origin of the document (email, whatsapp, api, manual, etc)');
            $table->foreign('source_id')->references('id')->on('document_sources')->onDelete('set null');

            // Add upload_id foreign key to document_upload_types
            $table->unsignedBigInteger('upload_id')->nullable()->after('source_id')->comment('How the document was uploaded (automatic, manual)');
            $table->foreign('upload_id')->references('id')->on('document_upload_types')->onDelete('set null');

            // Add indexes
            $table->index('source_id');
            $table->index('upload_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['source_id']);
            $table->dropForeign(['upload_id']);
            $table->dropColumn(['source_id', 'upload_id']);

            // Restore old columns
            $table->string('source')->nullable();
            $table->enum('upload_type', ['manual', 'automatic'])->default('automatic');
        });

        Schema::dropIfExists('document_upload_types');
    }
};
