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
        Schema::connection('helpdesk')->table('helpdesk_campaign_templates', function (Blueprint $table) {
            $table->enum('type', ['popup', 'banner', 'slide-in', 'full-screen'])->default('popup')->after('category');
            $table->string('preview_gradient')->nullable()->after('thumbnail_url');
            $table->boolean('is_premium')->default(false)->after('preview_gradient');
            $table->json('appearance')->nullable()->after('content'); // Default appearance settings
            $table->json('conditions')->nullable()->after('appearance'); // Default conditions
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('helpdesk')->table('helpdesk_campaign_templates', function (Blueprint $table) {
            $table->dropColumn(['type', 'preview_gradient', 'is_premium', 'appearance', 'conditions']);
        });
    }
};
