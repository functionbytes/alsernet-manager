<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "PrestaShop Password Reset", "Order Confirmation", etc
            $table->string('slug')->unique(); // "prestashop_password_reset", used in API route
            $table->string('source'); // "prestashop", "shopify", "custom", etc
            $table->string('type'); // "password_reset", "order_confirmation", "payment_rejected", etc
            $table->text('description')->nullable();

            // Associated template
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->onDelete('set null');
            $table->foreignId('lang_id')->nullable()->constrained('langs')->onDelete('set null');

            // Configuration
            $table->json('expected_variables')->nullable(); // ["customer_email", "customer_name", "order_id", etc]
            $table->json('required_variables')->nullable(); // Which ones are mandatory
            $table->json('variable_mappings')->nullable(); // How to map incoming JSON to template variables

            // Status
            $table->boolean('is_active')->default(true);
            $table->string('api_token')->unique()->nullable(); // Token for authentication

            // Tracking
            $table->unsignedBigInteger('requests_count')->default(0);
            $table->timestamp('last_request_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_endpoints');
    }
};
