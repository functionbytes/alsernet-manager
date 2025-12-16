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
        Schema::create('mail_variables', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('key')->unique(); // e.g., 'CUSTOMER_NAME', 'ORDER_ID'
            $table->string('name'); // Display name
            $table->text('description')->nullable(); // What this variable does
            $table->string('category')->default('general'); // Category: customer, order, document, system
            $table->string('module')->default('core'); // Module: documents, orders, core
            $table->boolean('is_system')->default(false); // System variables can't be deleted
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            // Indices for performance
            $table->index('module');
            $table->index('category');
            $table->index('is_enabled');
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_variables');
    }
};
