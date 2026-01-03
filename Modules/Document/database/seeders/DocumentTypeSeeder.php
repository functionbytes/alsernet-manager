<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Document\Entities\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Note: Translations are now managed via language files in resources/lang/{locale}/documents.php
     * This seeder only creates the DocumentType and DocumentRequirement records.
     */
    public function run(): void
    {
        $types = $this->getDocumentTypes();

        foreach ($types as $typeData) {
            // Create or update DocumentType
            $documentType = DocumentType::updateOrCreate(
                ['slug' => $typeData['slug']],
                [
                    'label' => $typeData['label'] ?? $typeData['slug'],
                    'icon' => $typeData['icon'] ?? null,
                    'color' => $typeData['color'] ?? null,
                    'is_active' => true,
                    'sort_order' => $typeData['sort_order'] ?? 0,
                    'sla_multiplier' => $typeData['sla_multiplier'] ?? 1.0,
                ]
            );

            // Create requirements for this document type
            foreach ($typeData['requirements'] as $index => $reqData) {
                $documentType->requirements()->updateOrCreate(
                    ['key' => $reqData['key']],
                    [
                        'is_required' => $reqData['is_required'] ?? true,
                        'accepts_multiple' => $reqData['accepts_multiple'] ?? false,
                        'max_file_size' => $reqData['max_file_size'] ?? 10240,
                        'allowed_extensions' => $reqData['allowed_extensions'] ?? ['pdf', 'jpg', 'jpeg', 'png'],
                        'sort_order' => $index,
                    ]
                );
            }
        }
    }

    /**
     * Get all document type definitions
     *
     * Note: Translations are stored in resources/lang/{locale}/documents.php
     */
    private function getDocumentTypes(): array
    {
        return [
            [
                'slug' => 'corta',
                'label' => 'Licencia de Caza (Corta)',
                'icon' => 'fa-gun',
                'color' => 'danger',
                'sort_order' => 1,
                'sla_multiplier' => 0.75,
                'requirements' => [
                    [
                        'key' => 'dni_frontal',
                        'is_required' => true,
                        'max_file_size' => 5120,
                        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                    ],
                    [
                        'key' => 'dni_trasera',
                        'is_required' => true,
                        'max_file_size' => 5120,
                        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                    ],
                    [
                        'key' => 'licencia_corta',
                        'is_required' => true,
                        'max_file_size' => 5120,
                        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                    ],
                ],
            ],
            [
                'slug' => 'rifle',
                'label' => 'Licencia de Caza (Rifle)',
                'icon' => 'fa-crosshairs',
                'color' => 'warning',
                'sort_order' => 2,
                'sla_multiplier' => 1.0,
                'requirements' => [
                    [
                        'key' => 'dni_frontal',
                        'is_required' => true,
                    ],
                    [
                        'key' => 'dni_trasera',
                        'is_required' => true,
                    ],
                    [
                        'key' => 'licencia_rifle',
                        'is_required' => true,
                    ],
                ],
            ],
            [
                'slug' => 'escopeta',
                'label' => 'Licencia de Caza (Escopeta)',
                'icon' => 'fa-burst',
                'color' => 'info',
                'sort_order' => 3,
                'sla_multiplier' => 1.0,
                'requirements' => [
                    [
                        'key' => 'dni_frontal',
                        'is_required' => true,
                    ],
                    [
                        'key' => 'dni_trasera',
                        'is_required' => true,
                    ],
                    [
                        'key' => 'licencia_escopeta',
                        'is_required' => true,
                    ],
                ],
            ],
            [
                'slug' => 'dni',
                'label' => 'Documento Nacional de Identidad',
                'icon' => 'fa-id-card',
                'color' => 'success',
                'sort_order' => 4,
                'sla_multiplier' => 0.5,
                'requirements' => [
                    [
                        'key' => 'dni_frontal',
                        'is_required' => true,
                    ],
                    [
                        'key' => 'dni_trasera',
                        'is_required' => true,
                    ],
                ],
            ],
            [
                'slug' => 'general',
                'label' => 'Documento General',
                'icon' => 'fa-file-alt',
                'color' => 'secondary',
                'sort_order' => 5,
                'sla_multiplier' => 1.0,
                'requirements' => [
                    [
                        'key' => 'documento',
                        'is_required' => true,
                    ],
                ],
            ],
            [
                'slug' => 'balines',
                'label' => 'Licencia de Balines',
                'icon' => 'fa-circle',
                'color' => 'warning',
                'sort_order' => 6,
                'sla_multiplier' => 0.5,
                'requirements' => [
                    [
                        'key' => 'dni_frontal',
                        'is_required' => true,
                        'max_file_size' => 5120,
                        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                    ],
                    [
                        'key' => 'dni_trasera',
                        'is_required' => true,
                        'max_file_size' => 5120,
                        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                    ],
                ],
            ],
        ];
    }
}
