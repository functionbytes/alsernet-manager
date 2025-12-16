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
        // Check if lang_id column exists before dropping
        if (Schema::hasColumn('email_templates', 'lang_id')) {
            Schema::table('email_templates', function (Blueprint $table) {
                // Drop the foreign key constraint first if it exists
                // Try different possible names for the foreign key
                try {
                    $table->dropForeign(['lang_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }

                // Then drop the column
                $table->dropColumn('lang_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->foreignId('lang_id')->nullable()->constrained('langs')->cascadeOnDelete();
        });
    }
};
