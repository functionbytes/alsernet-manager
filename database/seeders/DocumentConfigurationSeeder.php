<?php

namespace Database\Seeders;

use App\Models\Document\DocumentConfiguration;
use Illuminate\Database\Seeder;

class DocumentConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurations = [
            'corta' => [
                'label' => 'Armas Cortas',
                'documents' => [
                    'doc_1' => 'DNI - Cara delantera',
                    'doc_2' => 'DNI - Cara trasera',
                    'doc_3' => 'Licencia de armas cortas (tipo B) o licencia de tiro olímpico (tipo F)'
                ]
            ],
            'rifle' => [
                'label' => 'Rifles',
                'documents' => [
                    'doc_1' => 'DNI - Cara delantera',
                    'doc_2' => 'DNI - Cara trasera',
                    'doc_3' => 'Licencia de armas largas rayadas (tipo D)'
                ]
            ],
            'escopeta' => [
                'label' => 'Escopetas',
                'documents' => [
                    'doc_1' => 'DNI - Cara delantera',
                    'doc_2' => 'DNI - Cara trasera',
                    'doc_3' => 'Licencia de escopeta (tipo E)'
                ]
            ],
            'dni' => [
                'label' => 'Solo DNI',
                'documents' => [
                    'doc_1' => 'DNI - Cara delantera',
                    'doc_2' => 'DNI - Cara trasera'
                ]
            ],
            'general' => [
                'label' => 'General',
                'documents' => [
                    'doc_1' => 'Pasaporte o carnet de conducir (ambas caras si es tarjeta)'
                ]
            ]
        ];

        foreach ($configurations as $documentType => $config) {
            DocumentConfiguration::updateOrCreate(
                ['document_type' => $documentType],
                [
                    'document_type_label' => $config['label'],
                    'required_documents' => $config['documents']
                ]
            );
        }
    }
}
