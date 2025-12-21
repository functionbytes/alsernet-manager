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
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'type')) {
                $table->string('type')->nullable()->after('value');
            }
            if (! Schema::hasColumn('settings', 'label')) {
                $table->string('label')->nullable()->after('type');
            }
            if (! Schema::hasColumn('settings', 'description')) {
                $table->text('description')->nullable()->after('label');
            }
            if (! Schema::hasColumn('settings', 'group')) {
                $table->string('group')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['type', 'label', 'description', 'group']);
        });
    }
};
