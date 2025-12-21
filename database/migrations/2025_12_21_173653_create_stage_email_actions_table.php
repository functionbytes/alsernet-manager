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
        Schema::create('stage_email_actions', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique()->index();
            $table->string('validation_stage')->comment('documentacion, licencias, contabilidad');
            $table->string('email_action')->comment('solicitud_documentos, confirmacion_archivos, aprobacion, rechazo, correo_personalizado');
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['validation_stage', 'email_action']);
            $table->index('validation_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stage_email_actions');
    }
};
