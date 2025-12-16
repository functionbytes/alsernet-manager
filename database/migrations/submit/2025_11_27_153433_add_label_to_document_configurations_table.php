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
        Schema::table('request_document_configurations', function (Blueprint $table) {
            $table->string('document_type_label')->nullable()->after('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_document_configurations', function (Blueprint $table) {
            $table->dropColumn('document_type_label');
        });
    }
};
