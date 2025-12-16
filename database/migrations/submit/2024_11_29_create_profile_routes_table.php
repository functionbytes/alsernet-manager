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
        Schema::create('profile_routes', function (Blueprint $table) {
            $table->id();

            // Profile name (manager, shop, callcenter, etc.)
            $table->string('profile')->unique()->comment('Profile name');

            // Dashboard route name for this profile
            $table->string('dashboard_route')->comment('Route name for dashboard redirection');

            // Description
            $table->text('description')->nullable()->comment('Description of this profile route');

            $table->timestamps();

            $table->index('profile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_routes');
    }
};
