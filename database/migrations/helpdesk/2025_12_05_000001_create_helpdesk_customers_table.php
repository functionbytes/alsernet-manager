<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the helpdesk_customers table using default connection (webadminprueba)
     * No need to specify connection since we're using the default.
     */
    public function up(): void
    {
        Schema::create('helpdesk_customers', function (Blueprint $table) {
            $table->id();

            // Basic customer information
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('avatar_url')->nullable();

            // Location & demographic data
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();

            // Profile metadata
            $table->string('language')->default('en');
            $table->string('timezone')->nullable();
            $table->json('custom_attributes')->nullable();

            // Status tracking
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->text('ban_reason')->nullable();

            // Activity tracking
            $table->timestamp('last_seen_at')->nullable();
            $table->integer('total_conversations')->default(0);
            $table->integer('total_page_visits')->default(0);

            // Internal notes
            $table->longText('internal_notes')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('email');
            $table->index('created_at');
            $table->index('last_seen_at');
            $table->index('banned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_customers');
    }
};
