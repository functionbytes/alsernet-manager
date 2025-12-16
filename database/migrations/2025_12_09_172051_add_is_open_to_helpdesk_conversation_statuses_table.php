<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('helpdesk_conversation_statuses', function (Blueprint $table) {
            $table->boolean('is_open')->default(true)->after('is_system');
        });

        // Update existing statuses to set is_open based on their purpose
        DB::table('helpdesk_conversation_statuses')
            ->whereIn('slug', ['resolved', 'closed'])
            ->update(['is_open' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helpdesk_conversation_statuses', function (Blueprint $table) {
            $table->dropColumn('is_open');
        });
    }
};
