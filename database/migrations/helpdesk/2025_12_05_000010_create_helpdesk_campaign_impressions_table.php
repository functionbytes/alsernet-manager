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
        Schema::connection('mysql')->create('helpdesk_campaign_impressions', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('customer_session_id')->nullable();

            // Impression data
            $table->text('page_url');
            $table->enum('device_type', ['mobile', 'tablet', 'desktop'])->default('desktop')->index();
            $table->string('browser')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('country')->nullable()->index();

            // Interaction tracking
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('clicked_at')->nullable()->index();

            // Metadata
            $table->json('metadata')->nullable();

            // Timestamp (no update needed for impressions)
            $table->timestamp('created_at')->useCurrent();

            // Indexes for analytics queries
            $table->index(['campaign_id', 'created_at']);
            $table->index(['campaign_id', 'clicked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_campaign_impressions');
    }
};
