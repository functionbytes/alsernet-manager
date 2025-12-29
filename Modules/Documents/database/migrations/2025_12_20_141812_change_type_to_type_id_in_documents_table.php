<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Changes 'type' column (string slug) to 'type_id' (foreign key to document_types)
     */
    public function up(): void
    {
        // Step 1: Add new type_id column
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->nullable()->after('uid');
        });

        // Step 2: Migrate existing data from type (slug) to type_id
        DB::statement('
            UPDATE documents d
            INNER JOIN document_types dt ON dt.slug = d.type
            SET d.type_id = dt.id
            WHERE d.type IS NOT NULL
        ');

        // Step 3: Remove old type column
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        // Step 4: Add foreign key constraint
        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('type_id')->references('id')->on('document_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop foreign key
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
        });

        // Step 2: Add back type column
        Schema::table('documents', function (Blueprint $table) {
            $table->string('type')->nullable()->after('uid');
        });

        // Step 3: Migrate data back from type_id to type (slug)
        DB::statement('
            UPDATE documents d
            INNER JOIN document_types dt ON dt.id = d.type_id
            SET d.type = dt.slug
            WHERE d.type_id IS NOT NULL
        ');

        // Step 4: Remove type_id column
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('type_id');
        });
    }
};
