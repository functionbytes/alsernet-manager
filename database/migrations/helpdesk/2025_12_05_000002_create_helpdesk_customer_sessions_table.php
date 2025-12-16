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
        Schema::create('helpdesk_customer_sessions', function (Blueprint $table) {
            $table->id();

            // Foreign key
            $table->foreignId('customer_id')
                  ->constrained('helpdesk_customers')
                  ->onDelete('cascade');

            // Session data
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('session_token')->unique();

            // Geolocation
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Activity tracking
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('customer_id');
            $table->index('ip_address');
            $table->index('session_token');
            $table->index('last_activity_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_customer_sessions');
    }
};
