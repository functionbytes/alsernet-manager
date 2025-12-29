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
        Schema::create('supplier_automation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Configuration key');
            $table->text('value')->comment('Configuration value (can be JSON)');
            $table->enum('type', ['string', 'integer', 'boolean', 'json', 'encrypted'])->default('string');
            $table->string('category', 50)->comment('Category: connection, security, limits, defaults');
            $table->text('description')->nullable()->comment('Description for UI');
            $table->boolean('is_sensitive')->default(false)->comment('Hide in logs if sensitive');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();

            $table->index('category');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_automation_settings');
    }
};
