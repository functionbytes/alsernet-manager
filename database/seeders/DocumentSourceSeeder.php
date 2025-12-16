<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DocumentSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds the document_sources table with all available document sources.
     * Sources represent the channel/origin of where a document came from.
     */
    public function run(): void
    {
        $sources = [
            [
                'key' => 'manual',
                'label' => 'Manual',
                'description' => 'Documento cargado manualmente por el administrador',
                'icon' => 'pencil',
                'color' => '#6c757d',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'email',
                'label' => 'Email',
                'description' => 'Cliente envió el documento por email',
                'icon' => 'envelope',
                'color' => '#0d6efd',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'whatsapp',
                'label' => 'WhatsApp',
                'description' => 'Cliente envió el documento por WhatsApp',
                'icon' => 'phone',
                'color' => '#25d366',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'key' => 'prestashop',
                'label' => 'PrestaShop',
                'description' => 'Cliente cargó el documento desde el portal PrestaShop',
                'icon' => 'globe',
                'color' => '#24b9a6',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'key' => 'api',
                'label' => 'API',
                'description' => 'Documento cargado a través de integración API',
                'icon' => 'code',
                'color' => '#6f42c1',
                'is_active' => true,
                'order' => 5,
            ],
        ];

        foreach ($sources as $source) {
            \App\Models\Document\DocumentSource::firstOrCreate(
                ['key' => $source['key']],
                $source
            );
        }
    }
}
