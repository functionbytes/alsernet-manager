<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds missing fields to suppliers table to sync with Controller/View expectations
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Rename label to name for consistency with Controller/View
            $table->renameColumn('label', 'name');

            // Rename website_url to website for consistency
            $table->renameColumn('website_url', 'website');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            // Description and contact fields
            $table->text('description')->nullable()->after('website');
            $table->string('contact_email', 255)->nullable()->after('description');
            $table->string('contact_phone', 50)->nullable()->after('contact_email');

            // Priority and content type for the UI
            $table->integer('priority')->default(10)->after('contact_phone');
            $table->string('content_type', 50)->default('products')->after('priority');

            // API rate limiting
            $table->integer('api_rate_limit')->default(60)->after('content_type');
            $table->string('api_rate_period', 20)->default('minute')->after('api_rate_limit');

            // Metadata and configuration
            $table->json('metadata')->nullable()->after('api_rate_period');
            $table->json('config')->nullable()->after('metadata');

            // Sync tracking
            $table->timestamp('last_sync_at')->nullable()->after('config');

            // Add index for priority
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Remove new columns
            $table->dropIndex(['priority']);
            $table->dropColumn([
                'description',
                'contact_email',
                'contact_phone',
                'priority',
                'content_type',
                'api_rate_limit',
                'api_rate_period',
                'metadata',
                'config',
                'last_sync_at',
            ]);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            // Revert column renames
            $table->renameColumn('name', 'label');
            $table->renameColumn('website', 'website_url');
        });
    }
};
