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
        Schema::table('warehouse_locations', function (Blueprint $table) {
            // Visual dimension overrides (can differ from style base dimensions)
            $table->float('visual_width_m')->nullable()->after('position_y')->comment('Ancho visual final (metros)');
            $table->float('visual_height_m')->nullable()->after('visual_width_m')->comment('Alto visual final (metros)');

            // Visual position overrides (can differ from base position)
            $table->float('visual_position_x')->nullable()->after('visual_height_m')->comment('Posición X visual (metros)');
            $table->float('visual_position_y')->nullable()->after('visual_position_x')->comment('Posición Y visual (metros)');

            // Flag to use custom visual values instead of style defaults
            $table->boolean('use_custom_visual')->default(false)->after('visual_position_y')->comment('Usar dimensiones custom visuales');

            // Visual rotation in degrees (future enhancement)
            $table->float('visual_rotation')->default(0)->after('use_custom_visual')->comment('Rotación visual en grados (0-360)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_locations', function (Blueprint $table) {
            $table->dropColumn([
                'visual_width_m',
                'visual_height_m',
                'visual_position_x',
                'visual_position_y',
                'use_custom_visual',
                'visual_rotation',
            ]);
        });
    }
};
