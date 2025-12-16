<?php

namespace Database\Seeders;

use App\Models\Document\DocumentType;
use Illuminate\Database\Seeder;

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
            // Create DocumentType
            $documentType = DocumentType::create([
                'slug' => $typeData['slug'],
                'icon' => $typeData['icon'] ?? null,
                'color' => $typeData['color'] ?? null,
                'is_active' => true,
                'sort_order' => $typeData['sort_order'] ?? 0,
                'sla_multiplier' => $typeData['sla_multiplier'] ?? 1.0,
            ]);

            // Create requirements for this document type
            foreach ($typeData['requirements'] as $index => $reqData) {
                $documentType->requirements()->create([
                    'key' => $reqData['key'],
                    'is_required' => $reqData['is_required'] ?? true,
                    'accepts_multiple' => $reqData['accepts_multiple'] ?? false,
                    'max_file_size' => $reqData['max_file_size'] ?? 10240,
                    'allowed_extensions' => $reqData['allowed_extensions'] ?? ['pdf', 'jpg', 'jpeg', 'png'],
                    'sort_order' => $index,
                ]);
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
        ];
    }
}
