<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add validation_stages field to document_types table.
     * This allows configuring validation workflow stages per document type.
     */
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->json('validation_stages')->nullable()->after('sla_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('validation_stages');
        });
    }
};
