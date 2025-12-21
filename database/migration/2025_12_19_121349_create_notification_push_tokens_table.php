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
        Schema::create('notification_push_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Token del dispositivo (único por navegador)
            $table->text('token'); // Token FCM o similar
            $table->string('device_type')->nullable(); // 'desktop', 'mobile', 'tablet'
            $table->string('browser')->nullable(); // 'Chrome', 'Firefox', 'Safari'
            $table->string('platform')->nullable(); // 'Windows', 'macOS', 'Android'

            // Metadata
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->ipAddress('ip_address')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_push_tokens');
    }
};
