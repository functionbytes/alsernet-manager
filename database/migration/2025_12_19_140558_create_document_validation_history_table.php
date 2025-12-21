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
        Schema::create('document_validation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->unsignedInteger('stage_number'); // Stage 1, 2, 3, etc.
            $table->string('validator_group', 100); // administrative, manager, accounting, etc.
            $table->foreignId('validator_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action', 50); // approved, rejected, returned
            $table->text('comments')->nullable();
            $table->timestamp('validated_at');
            $table->timestamps();

            // Indexes
            $table->index(['document_id', 'stage_number'], 'idx_document_stage');
            $table->index('validator_user_id', 'idx_validator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_validation_history');
    }
};
