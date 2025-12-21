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
        Schema::table('request_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('lang_id')->nullable()->after('type');
            $table->foreign('lang_id')->references('id')->on('langs')->onDelete('set null');
            $table->index('lang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            $table->dropForeign(['lang_id']);
            $table->dropIndex(['lang_id']);
            $table->dropColumn('lang_id');
        });
    }
};
