<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Polymorphic validation history table for HasValidationWorkflow trait.
     * Can be used by: documents, tickets, returns, etc.
     */
    public function up(): void
    {
        Schema::create('document_validation_histories', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 36)->unique();

            // Polymorphic relationship
            $table->morphs('validatable'); // Creates validatable_type and validatable_id

            // Validation details
            $table->unsignedInteger('stage_number');
            $table->string('validator_group'); // ValidatorGroup key
            $table->unsignedBigInteger('validator_user_id');
            $table->enum('action', ['approved', 'rejected', 'returned', 'reopened']);
            $table->text('comments')->nullable();
            $table->timestamp('validated_at');

            $table->timestamps();

            // Foreign key
            $table->foreign('validator_user_id')->references('id')->on('users')->cascadeOnDelete();

            // Indexes for performance
            $table->index(['validatable_type', 'validatable_id'], 'idx_validatable');
            $table->index('validator_group');
            $table->index('validator_user_id');
            $table->index('validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_validation_histories');
    }
};
