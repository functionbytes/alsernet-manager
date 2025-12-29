<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->morphs('model');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['permission_id', 'model_id', 'model_type'], 'idx_permission_model');
            $table->index('model_id');
            $table->index('model_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_has_permissions');
    }
};
