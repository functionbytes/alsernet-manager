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
        Schema::table('documents', function (Blueprint $table) {
            // Financing requirement
            $table->boolean('requires_financing')->default(false)->after('type');

            // Workflow tracking
            $table->string('validation_status', 50)->default('pending')->after('requires_financing');
            // Status: pending, in_validation, approved, rejected

            $table->unsignedInteger('current_stage')->default(1)->after('validation_status');
            // Which stage is the document currently at (1, 2, 3, etc.)

            $table->unsignedInteger('total_stages')->default(1)->after('current_stage');
            // Total stages this document needs to pass through

            $table->string('current_validator_group', 100)->nullable()->after('total_stages');
            // Which group should validate the document at this stage (administrative, manager, accounting, etc.)

            $table->timestamp('validation_started_at')->nullable()->after('current_validator_group');
            $table->timestamp('validation_completed_at')->nullable()->after('validation_started_at');

            // Index for queries
            $table->index(['validation_status', 'current_validator_group'], 'idx_validation_queue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('idx_validation_queue');
            $table->dropColumn([
                'requires_financing',
                'validation_status',
                'current_stage',
                'total_stages',
                'current_validator_group',
                'validation_started_at',
                'validation_completed_at',
            ]);
        });
    }
};
