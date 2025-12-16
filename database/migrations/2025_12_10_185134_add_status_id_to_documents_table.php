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
            $table->foreignId('status_id')->nullable()->after('id')->constrained('document_statuses')->nullOnDelete();
            $table->index('status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            $table->dropForeignIdFor('status_id');
            $table->dropColumn('status_id');
        });
    }
};
