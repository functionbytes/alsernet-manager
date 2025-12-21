<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Generate UUIDs for all existing layout translations without UIDs
     */
    public function up(): void
    {
        $translations = DB::table('layout_translations')
            ->whereNull('uid')
            ->get();

        foreach ($translations as $translation) {
            DB::table('layout_translations')
                ->where('id', $translation->id)
                ->update(['uid' => Str::uuid()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all UIDs back to null
        DB::table('layout_translations')->update(['uid' => null]);
    }
};
