<?php

namespace Database\Seeders\Documents;

use App\Models\Document\DocumentValidationCondition;
use Illuminate\Database\Seeder;

class DocumentValidationConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds the default validation conditions for document workflows.
     * These map condition keys (used in validation_stages) to arrays of sale_types
     * from the document_product_blockades table.
     */
    public function run(): void
    {
        $conditions = [
            [
                'key' => 'is_weapon',
                'name' => 'Es un arma',
                'description' => 'Documentos que requieren validación de licencias de armas. Incluye escopetas, rifles y armas cortas.',
                'sale_types' => ['escopeta', 'rifle', 'corta', 'armas'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'is_dni_only',
                'name' => 'Requiere solo DNI',
                'description' => 'Documentos que solo requieren validación de DNI/identificación, sin necesidad de licencias adicionales.',
                'sale_types' => ['dni'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'requires_financing',
                'name' => 'Requiere financiación',
                'description' => 'Documentos que incluyen financiación y requieren validación contable adicional.',
                'sale_types' => [], // This condition is checked via document.requires_financing field, not sale_type
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($conditions as $conditionData) {
            DocumentValidationCondition::updateOrCreate(
                ['key' => $conditionData['key']],
                $conditionData
            );
        }

        $this->command->info('✓ Document validation conditions seeded successfully.');
    }
}
