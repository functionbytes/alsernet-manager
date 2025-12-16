<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            $table->string('customer_cellphone', 32)->nullable()
                ->after('customer_company')
                ->comment('Customer mobile phone from order address');

            // Add index for customer_cellphone
            $table->index('customer_cellphone');
        });
    }

    public function down(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            $table->dropIndex(['customer_cellphone']);
            $table->dropColumn('customer_cellphone');
        });
    }
};
