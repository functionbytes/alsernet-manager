<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Separates two distinct concepts:
     * 1. Source (channel): document_source_id (email, whatsapp, prestashop, api, manual)
     * 2. Upload Type: upload_type string field (manual, automatic)
     */
    public function up(): void
    {
        // Create document_sources table
        Schema::create('document_sources', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Identifier (email, whatsapp, prestashop, api, manual)');
            $table->string('label')->comment('Display name');
            $table->string('description')->nullable()->comment('Description of the source');
            $table->string('icon')->nullable()->comment('Icon class');
            $table->string('color')->nullable()->comment('Color code');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->index('is_active');
        });

        // Add source_id and upload_type to documents
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('document_source_id')->nullable()->after('source')->comment('Channel/origin of the document');
            $table->enum('upload_type', ['manual', 'automatic'])->default('automatic')->after('source')->comment('How the document was uploaded');

            // Add foreign key
            $table->foreign('document_source_id')->references('id')->on('document_sources')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['document_source_id']);
            $table->dropColumn(['document_source_id', 'upload_type']);
        });

        Schema::dropIfExists('document_sources');
    }
};
