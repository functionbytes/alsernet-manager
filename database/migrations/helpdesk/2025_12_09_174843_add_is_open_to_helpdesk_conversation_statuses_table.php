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
        Schema::connection('mysql')->table('helpdesk_conversation_statuses', function (Blueprint $table) {
            $table->boolean('is_open')->default(false)->after('is_system')->index();
        });

        // Update existing records based on their slug
        // Open statuses: open, waiting_customer, on_hold
        DB::connection('mysql')->table('helpdesk_conversation_statuses')
            ->whereIn('slug', ['open', 'waiting_customer', 'on_hold'])
            ->update(['is_open' => true]);

        // Closed statuses: resolved, closed
        DB::connection('mysql')->table('helpdesk_conversation_statuses')
            ->whereIn('slug', ['resolved', 'closed'])
            ->update(['is_open' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->table('helpdesk_conversation_statuses', function (Blueprint $table) {
            $table->dropIndex(['is_open']);
            $table->dropColumn('is_open');
        });
    }
};
