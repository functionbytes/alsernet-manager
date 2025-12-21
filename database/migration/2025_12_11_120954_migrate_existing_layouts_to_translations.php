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
        // Migrar datos existentes de layouts a layout_translations
        // Agrupar por alias para identificar layouts únicos
        $layouts = DB::table('layouts')->get();

        // Agrupar layouts por alias (mismo componente, diferentes idiomas)
        $groupedByAlias = $layouts->groupBy('alias');

        foreach ($groupedByAlias as $alias => $layoutGroup) {
            // El primer layout del grupo será el "maestro"
            $masterLayout = $layoutGroup->first();

            // Crear traducciones para cada idioma
            foreach ($layoutGroup as $layout) {
                DB::table('layout_translations')->insert([
                    'layout_id' => $masterLayout->id,
                    'lang_id' => $layout->lang_id ?? 1, // Default a idioma 1 si no tiene
                    'subject' => $layout->subject,
                    'content' => $layout->content,
                    'created_at' => $layout->created_at,
                    'updated_at' => $layout->updated_at,
                ]);
            }

            // Si había múltiples layouts con el mismo alias, eliminar los duplicados
            // (manteniendo solo el maestro)
            if ($layoutGroup->count() > 1) {
                $idsToDelete = $layoutGroup->pluck('id')->slice(1)->toArray();
                if (! empty($idsToDelete)) {
                    DB::table('layouts')->whereIn('id', $idsToDelete)->delete();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es posible revertir completamente esta migración
        // ya que perdemos información al consolidar layouts
        DB::table('layout_translations')->truncate();
    }
};
