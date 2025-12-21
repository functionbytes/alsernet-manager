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
        Schema::connection('mysql')->create('helpdesk_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('key', 60)->unique();
            $table->enum('type', ['conversation', 'customer', 'ticket'])->default('conversation');
            $table->string('format', 50); // text, textarea, number, switch, rating, select, checkboxGroup, date
            $table->boolean('required')->default(false);
            $table->enum('permission', ['userCanView', 'userCanEdit', 'agentCanEdit'])->default('agentCanEdit');
            $table->text('description')->nullable();
            $table->string('customer_name', 60)->nullable();
            $table->string('customer_description', 300)->nullable();
            $table->json('config')->nullable(); // options for select/checkbox, min/max for number, etc.
            $table->boolean('internal')->default(false);
            $table->boolean('materialized')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('active');
            $table->index(['type', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_attributes');
    }
};
