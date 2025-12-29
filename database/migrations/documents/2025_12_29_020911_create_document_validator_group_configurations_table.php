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
        Schema::create('document_validator_group_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_group_id')->constrained()->cascadeOnDelete();
            $table->string('key')->comment('Configuration key (e.g., enable_auto_approval)');
            $table->string('label')->comment('Display label');
            $table->text('description')->nullable()->comment('Description of the configuration');
            $table->boolean('value')->default(false)->comment('Configuration value');
            $table->string('category')->nullable()->comment('Category (e.g., email_actions, workflow)');
            $table->integer('order')->default(0)->comment('Sort order');
            $table->boolean('is_active')->default(true)->comment('Whether this config is active');
            $table->timestamps();

            // Indexes
            $table->unique(['validator_group_id', 'key']);
            $table->index(['validator_group_id', 'category']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_validator_group_configurations');
    }
};
