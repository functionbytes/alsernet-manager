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
        Schema::create('role_mappings', function (Blueprint $table) {
            $table->id();

            // Profile name (manager, shop, callcenter, etc.)
            $table->string('profile')->unique()->comment('Profile/Route group name');

            // Roles allowed for this profile (JSON array)
            $table->json('roles')->comment('Array of role names allowed for this profile');

            // Description
            $table->text('description')->nullable()->comment('Description of this profile and its access');

            // Active/Inactive
            $table->boolean('is_active')->default(true)->comment('If this mapping is active');

            $table->timestamps();

            // Indexes
            $table->index('profile');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_mappings');
    }
};
