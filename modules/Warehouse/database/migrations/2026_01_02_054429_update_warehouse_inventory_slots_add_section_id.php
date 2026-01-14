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
        Schema::table('warehouse_inventory_slots', function (Blueprint $table) {
            // Drop the unique constraint that includes the old 'section' column
            $table->dropUnique(['location_id', 'face', 'level', 'section']);

            // Drop the old 'section' column
            $table->dropColumn('section');

            // Add new foreign key columns
            $table->foreignId('section_id')
                ->nullable()
                ->after('level')
                ->constrained('warehouse_location_sections')
                ->onDelete('set null');

            $table->foreignId('last_section_id')
                ->nullable()
                ->after('last_warehouse_id')
                ->constrained('warehouse_location_sections')
                ->onDelete('set null');

            // Add new unique constraint
            $table->unique(['location_id', 'face', 'level', 'section_id'], 'inventory_slot_unique');

            // Add indexes for the new columns
            $table->index('section_id');
            $table->index('last_section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite doesn't support dropping foreign keys
        if (config('database.default') !== 'sqlite') {
            Schema::table('warehouse_inventory_slots', function (Blueprint $table) {
                // Drop new constraints and columns
                $table->dropUnique('inventory_slot_unique');
                $table->dropForeign(['section_id']);
                $table->dropForeign(['last_section_id']);
                $table->dropColumn(['section_id', 'last_section_id']);

                // Restore old column
                $table->integer('section')->default(1)->after('level');
                $table->unique(['location_id', 'face', 'level', 'section']);
            });
        }
    }
};
