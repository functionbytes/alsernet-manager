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
        Schema::connection('mysql')->create('helpdesk_attributables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('helpdesk_attributes')->onDelete('cascade');
            $table->morphs('attributable'); // attributable_id, attributable_type
            $table->text('value')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['attribute_id', 'attributable_type']);
            $table->index(['attributable_id', 'attributable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_attributables');
    }
};
