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
        Schema::table('layout_translations', function (Blueprint $table) {
            $table->uuid('uid')->unique()->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layout_translations', function (Blueprint $table) {
            $table->dropUnique(['uid']);
            $table->dropColumn('uid');
        });
    }
};
