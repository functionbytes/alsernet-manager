<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_validation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->integer('stage_number')->default(1);
            $table->string('validator_group');
            $table->unsignedBigInteger('validator_user_id')->nullable();
            $table->string('action');
            $table->text('comments')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->index('document_id');
            $table->index('validator_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_validation_history');
    }
};
