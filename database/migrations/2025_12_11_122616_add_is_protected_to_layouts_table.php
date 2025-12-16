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
        Schema::table('layouts', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('group_name');
        });

        // Mark system components as protected
        DB::table('layouts')
            ->whereIn('alias', [
                'email_template_header',
                'email_template_footer',
                'email_template_wrapper',
            ])
            ->update(['is_protected' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layouts', function (Blueprint $table) {
            $table->dropColumn('is_protected');
        });
    }
};
