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
        Schema::table('roles', function (Blueprint $table) {
            // Add label and description columns if they don't exist
            if (!Schema::hasColumn('roles', 'label')) {
                $table->string('label')->nullable()->after('name')
                    ->comment('Readable label for the role (e.g., "Super Administrator")');
            }

            if (!Schema::hasColumn('roles', 'description')) {
                $table->text('description')->nullable()->after('label')
                    ->comment('Detailed description of what this role can do');
            }

            if (!Schema::hasColumn('roles', 'color')) {
                $table->string('color')->nullable()->after('description')
                    ->comment('Color code for UI (e.g., #FF0000 for red)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'label')) {
                $table->dropColumn('label');
            }
            if (Schema::hasColumn('roles', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('roles', 'color')) {
                $table->dropColumn('color');
            }
        });
    }
};
