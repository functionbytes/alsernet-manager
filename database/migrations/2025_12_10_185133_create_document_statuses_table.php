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
        Schema::create('document_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // pending, incomplete, awaiting, approved, completed, rejected, cancelled
            $table->string('label'); // Display name
            $table->text('description')->nullable();
            $table->string('color', 10)->default('#6c757d'); // Hex color for UI
            $table->string('icon', 50)->default('circle'); // Tabler icon name
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_statuses');
    }
};
