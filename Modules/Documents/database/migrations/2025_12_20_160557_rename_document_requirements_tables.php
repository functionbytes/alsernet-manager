<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename document_requirements to document_type_requirements
        Schema::rename('document_requirements', 'document_type_requirements');

        // Rename document_requirement_translations to document_type_requirement_translations
        Schema::rename('document_requirement_translations', 'document_type_requirement_translations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the renames
        Schema::rename('document_type_requirements', 'document_requirements');
        Schema::rename('document_type_requirement_translations', 'document_requirement_translations');
    }
};
