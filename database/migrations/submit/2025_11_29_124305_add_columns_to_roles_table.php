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
            // Add description column
            if (!Schema::hasColumn('roles', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            // Add slug column
            if (!Schema::hasColumn('roles', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('description');
            }

            // Add is_default column
            if (!Schema::hasColumn('roles', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('slug');
            }

            // Add created_by column (user who created the role)
            if (!Schema::hasColumn('roles', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('is_default');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }

            // Add updated_by column (user who last updated the role)
            if (!Schema::hasColumn('roles', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('roles', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
            if (Schema::hasColumn('roles', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }

            // Drop columns
            if (Schema::hasColumn('roles', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('roles', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('roles', 'is_default')) {
                $table->dropColumn('is_default');
            }
            if (Schema::hasColumn('roles', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('roles', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }
};
