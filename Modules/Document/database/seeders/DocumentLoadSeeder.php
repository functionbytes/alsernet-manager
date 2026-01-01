<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;

class DocumentLoadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds the document_loads table with all available document load types.
     * Loads represent how/when documents are loaded (e.g., on demand, scheduled).
     */
    public function run(): void
    {
        $loads = [
            [
                'key' => 'manual',
                'label' => 'Manual',
                'description' => 'Carga manual de documentos por el administrador',
                'icon' => 'pencil',
                'color' => '#6c757d',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'on_demand',
                'label' => 'Bajo Demanda',
                'description' => 'Documentos cargados cuando se solicita (por cliente o sistema)',
                'icon' => 'download',
                'color' => '#0d6efd',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'scheduled',
                'label' => 'Programado',
                'description' => 'Carga automática de documentos en horarios establecidos',
                'icon' => 'clock',
                'color' => '#17a2b8',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'key' => 'automated',
                'label' => 'Automático',
                'description' => 'Carga automática disparada por eventos o condiciones',
                'icon' => 'zap',
                'color' => '#ffc107',
                'is_active' => true,
                'order' => 4,
            ],
        ];

        foreach ($loads as $load) {
            \App\Models\Document\DocumentLoad::firstOrCreate(
                ['key' => $load['key']],
                $load
            );
        }
    }
}
