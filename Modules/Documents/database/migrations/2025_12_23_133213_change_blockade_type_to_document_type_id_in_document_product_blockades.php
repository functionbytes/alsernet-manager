<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add the new column as nullable
        Schema::table('document_product_blockades', function (Blueprint $table) {
            $table->unsignedBigInteger('document_type_id')->nullable()->after('source_id');
        });

        // Step 2: Migrate existing data from blockade_type (slug) to document_type_id
        $this->migrateBlockadeTypeToDocumentTypeId();

        // Step 3: Make document_type_id NOT NULL and add foreign key
        Schema::table('document_product_blockades', function (Blueprint $table) {
            $table->unsignedBigInteger('document_type_id')->nullable(false)->change();
            $table->foreign('document_type_id')
                ->references('id')
                ->on('document_types')
                ->onDelete('restrict');
        });

        // Step 4: Drop the old blockade_type column
        Schema::table('document_product_blockades', function (Blueprint $table) {
            $table->dropColumn('blockade_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add back the blockade_type column
        Schema::table('document_product_blockades', function (Blueprint $table) {
            $table->string('blockade_type')->nullable()->after('source_id');
        });

        // Step 2: Migrate data back from document_type_id to blockade_type
        $this->migrateDocumentTypeIdToBlockadeType();

        // Step 3: Drop the foreign key and document_type_id column
        Schema::table('document_product_blockades', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']);
            $table->dropColumn('document_type_id');
        });
    }

    /**
     * Migrate blockade_type (slug) to document_type_id (foreign key)
     */
    private function migrateBlockadeTypeToDocumentTypeId(): void
    {
        // Get all document types with their IDs and slugs
        $documentTypes = DB::table('document_types')
            ->select('id', 'slug')
            ->get()
            ->keyBy('slug');

        // Get all blockades with their current blockade_type
        $blockades = DB::table('document_product_blockades')
            ->select('id', 'blockade_type')
            ->whereNotNull('blockade_type')
            ->get();

        // Update each blockade with the corresponding document_type_id
        foreach ($blockades as $blockade) {
            $documentType = $documentTypes->get($blockade->blockade_type);

            if ($documentType) {
                DB::table('document_product_blockades')
                    ->where('id', $blockade->id)
                    ->update(['document_type_id' => $documentType->id]);
            } else {
                // Log warning if blockade_type doesn't match any document_type slug
                logger()->warning("DocumentProductBlockade #{$blockade->id} has invalid blockade_type: {$blockade->blockade_type}");
            }
        }
    }

    /**
     * Migrate document_type_id back to blockade_type (slug) for rollback
     */
    private function migrateDocumentTypeIdToBlockadeType(): void
    {
        // Get all blockades with their document_type_id
        $blockades = DB::table('document_product_blockades')
            ->select('document_product_blockades.id', 'document_types.slug')
            ->join('document_types', 'document_product_blockades.document_type_id', '=', 'document_types.id')
            ->get();

        // Update each blockade with the corresponding slug
        foreach ($blockades as $blockade) {
            DB::table('document_product_blockades')
                ->where('id', $blockade->id)
                ->update(['blockade_type' => $blockade->slug]);
        }
    }
};
