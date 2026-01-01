<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Document\Entities\ValidatorGroup;

class DocumentValidatorGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds the document_validator_groups table with default validation teams.
     * These groups are used in the document validation workflow:
     * - Documentation Team: Initial validation (stage 1)
     * - Licenses Team: License verification (stage 2)
     * - Accounting Team: Final approval (stage 3)
     *
     * Each group can have multiple users assigned with different priorities.
     * Groups control which users can approve/reject at each validation stage.
     */
    public function run(): void
    {
        $groups = [
            [
                'uid' => Str::ulid(),
                'name' => 'Equipo Documentación',
                'key' => 'documentation_team',
                'description' => 'Responsables de la validación inicial de documentos',
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'uid' => Str::ulid(),
                'name' => 'Equipo Licencias',
                'key' => 'licenses_team',
                'description' => 'Responsables de la validación de licencias y permisos',
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'uid' => Str::ulid(),
                'name' => 'Equipo Contabilidad',
                'key' => 'accounting_team',
                'description' => 'Responsables de la aprobación final y registros contables',
                'priority' => 3,
                'is_active' => true,
            ],
            [
                'uid' => Str::ulid(),
                'name' => 'Equipo Legal',
                'key' => 'legal_team',
                'description' => 'Validación de aspectos legales y términos',
                'priority' => 4,
                'is_active' => false, // Disabled by default
            ],
        ];

        foreach ($groups as $groupData) {
            ValidatorGroup::firstOrCreate(
                ['key' => $groupData['key']],
                $groupData
            );
        }

        $this->command->info('✅ Document validator groups seeded successfully');
    }
}
