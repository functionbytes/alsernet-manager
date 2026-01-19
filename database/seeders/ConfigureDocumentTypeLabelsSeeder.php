<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Document\Entities\DocumentType;

class ConfigureDocumentTypeLabelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configure associated labels for each document type
        // These labels come from PrestaShop and determine which products require this document type

        $configuration = [
            'Licencia de armas cortas' => ['CORTA'],
            'Licencia de rifles' => ['RIFLE'],
            'Licencia de escopetas' => ['ESCOPETA'],
            'Solo identificación' => ['DNI', 'BALINES45', 'BALINES55', 'BALINES635'],
        ];

        foreach ($configuration as $documentTypeLabel => $labels) {
            $docType = DocumentType::where('label', $documentTypeLabel)->first();

            if ($docType) {
                $docType->update(['associated_labels' => $labels]);
                $this->command->info("✓ {$documentTypeLabel}: ".implode(', ', $labels));
            } else {
                $this->command->warn("⚠ Document type '{$documentTypeLabel}' not found");
            }
        }
    }
}
