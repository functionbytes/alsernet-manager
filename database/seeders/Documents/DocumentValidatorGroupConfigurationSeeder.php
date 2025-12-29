<?php

namespace Database\Seeders\Documents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Documents\Entities\ValidatorGroup;
use Modules\Documents\Entities\ValidatorGroupConfiguration;

class DocumentValidatorGroupConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds the document_validator_group_configurations table with validation rules
     * for each validator group.
     *
     * Configuration defines:
     * - Required number of approvals before moving to next stage
     * - Document types that require this group's validation
     * - Approval timeout period
     * - Whether rejection is allowed
     * - Notification settings
     *
     * Depends on: DocumentValidatorGroupSeeder (must run first)
     */
    public function run(): void
    {
        // Get validator groups
        $docTeam = ValidatorGroup::where('key', 'documentation_team')->first();
        $licenseTeam = ValidatorGroup::where('key', 'licenses_team')->first();
        $accountingTeam = ValidatorGroup::where('key', 'accounting_team')->first();

        if (! $docTeam || ! $licenseTeam || ! $accountingTeam) {
            $this->command->error('❌ Validator groups not found. Run DocumentValidatorGroupSeeder first!');

            return;
        }

        $configurations = [
            // Documentation Team Config
            [
                'uid' => Str::ulid(),
                'validator_group_id' => $docTeam->id,
                'document_types' => json_encode(['*']), // All document types
                'required_approvals' => 1,
                'approval_timeout_days' => 5,
                'allow_rejection' => true,
                'rejection_requires_reason' => true,
                'auto_escalate_on_timeout' => true,
                'notify_on_assignment' => true,
                'notify_on_expiration' => true,
                'priority' => 'high',
                'metadata' => json_encode([
                    'stage' => 1,
                    'stage_name' => 'Validación Inicial',
                    'check_completeness' => true,
                    'check_signatures' => false,
                ]),
                'is_active' => true,
            ],

            // Licenses Team Config
            [
                'uid' => Str::ulid(),
                'validator_group_id' => $licenseTeam->id,
                'document_types' => json_encode(['license', 'permit', 'authorization']),
                'required_approvals' => 1,
                'approval_timeout_days' => 7,
                'allow_rejection' => true,
                'rejection_requires_reason' => true,
                'auto_escalate_on_timeout' => true,
                'notify_on_assignment' => true,
                'notify_on_expiration' => true,
                'priority' => 'medium',
                'metadata' => json_encode([
                    'stage' => 2,
                    'stage_name' => 'Validación de Licencias',
                    'check_expiration' => true,
                    'check_authenticity' => true,
                    'external_verification_required' => true,
                ]),
                'is_active' => true,
            ],

            // Accounting Team Config
            [
                'uid' => Str::ulid(),
                'validator_group_id' => $accountingTeam->id,
                'document_types' => json_encode(['*']), // All types
                'required_approvals' => 1,
                'approval_timeout_days' => 3,
                'allow_rejection' => true,
                'rejection_requires_reason' => true,
                'auto_escalate_on_timeout' => false, // Final stage, don't escalate
                'notify_on_assignment' => true,
                'notify_on_expiration' => true,
                'priority' => 'critical',
                'metadata' => json_encode([
                    'stage' => 3,
                    'stage_name' => 'Aprobación Final',
                    'creates_accounting_entry' => true,
                    'sends_approval_email' => true,
                    'requires_digital_signature' => false,
                ]),
                'is_active' => true,
            ],
        ];

        foreach ($configurations as $config) {
            ValidatorGroupConfiguration::firstOrCreate(
                [
                    'validator_group_id' => $config['validator_group_id'],
                    'document_types' => $config['document_types'],
                ],
                $config
            );
        }

        $this->command->info('✅ Document validator group configurations seeded successfully');
    }
}
