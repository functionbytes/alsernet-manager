<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // role_mappings
        Schema::create('role_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('profile')->unique();
            $table->json('roles')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // route_permissions
        Schema::create('route_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('app_routes')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_permissions');
        Schema::dropIfExists('role_mappings');
    }
};
