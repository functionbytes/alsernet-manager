<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validator_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_group_id')->constrained('validator_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('priority', ['primary', 'backup'])->default('primary');
            $table->timestamp('created_at')->nullable();

            $table->unique(['validator_group_id', 'user_id'], 'unique_validator_group_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_validator_group_user');
    }
};
