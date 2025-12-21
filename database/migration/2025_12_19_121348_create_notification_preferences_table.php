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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Canal y tipo de notificación
            $table->enum('channel', ['in_app', 'push', 'email', 'sms']);
            $table->string('notification_type'); // 'order.*', 'document.status_changed', 'ticket.assigned'

            // Estado: ¿Habilitado o no?
            $table->boolean('is_enabled')->default(true);

            // Configuración adicional (JSON flexible)
            $table->json('settings')->nullable(); // { "digest": "daily", "quiet_hours": {...} }

            $table->timestamps();

            // Un usuario solo tiene una configuración por canal+tipo
            $table->unique(['user_id', 'channel', 'notification_type'], 'user_channel_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
