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
        Schema::create('app_routes', function (Blueprint $table) {
            $table->id();

            // Route information
            $table->string('name')->unique()->comment('Route name (e.g., manager.users.edit)');
            $table->string('path')->comment('Route path (e.g., /manager/users/{uid}/edit)');
            $table->string('method')->comment('HTTP method (GET, POST, PUT, DELETE)');

            // Profile information
            $table->string('profile')->nullable()->comment('Route profile (manager, callcenter, shop, etc.)');

            // Middleware and execution
            $table->json('middleware')->nullable()->comment('Route middleware array');
            $table->string('controller')->nullable()->comment('Controller class');
            $table->string('action')->nullable()->comment('Controller action');

            // Metadata
            $table->text('description')->nullable();
            $table->boolean('requires_auth')->default(true);
            $table->boolean('is_active')->default(true)->comment('Is route active/enabled');
            $table->string('hash')->unique()->comment('Hash for detecting route changes');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('profile');
            $table->index('name');
            $table->index('is_active');
            $table->index('method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_routes');
    }
};
