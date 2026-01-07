<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener todos los idiomas disponibles
        $allLangs = DB::table('langs')->where('available', 1)->get();

        // Obtener todos los layouts de mail_templates
        $layouts = DB::table('mailer_layouts')
            ->where('group_name', 'mail_templates')
            ->get();

        foreach ($layouts as $layout) {
            // Obtener idiomas que ya tienen traducción
            $existingLangIds = DB::table('mailer_layout_langs')
                ->where('layout_id', $layout->id)
                ->pluck('lang_id')
                ->toArray();

            // Crear traducciones para idiomas faltantes
            foreach ($allLangs as $lang) {
                if (!in_array($lang->id, $existingLangIds)) {
                    DB::table('mailer_layout_langs')->insert([
                        'layout_id' => $layout->id,
                        'lang_id' => $lang->id,
                        'subject' => '',
                        'content' => '',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertimos esta migración ya que las traducciones vacías no causan problemas
    }
};
