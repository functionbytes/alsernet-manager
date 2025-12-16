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
        Schema::create('route_permissions', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('route_id')
                ->constrained('app_routes')
                ->onDelete('cascade')
                ->comment('Reference to app_routes table');

            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->onDelete('cascade')
                ->comment('Reference to permissions table');

            // Composite unique index to prevent duplicates
            $table->unique(['route_id', 'permission_id']);

            // Indexes
            $table->index('route_id');
            $table->index('permission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_permissions');
    }
};
