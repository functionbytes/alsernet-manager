<?php

namespace Database\Seeders\Documents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Document\Entities\StageEmailAction;

class StageEmailActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Documentación (Etapa 1) - All email actions enabled
        $this->createStageActions(
            StageEmailAction::STAGE_DOCUMENTACION,
            [
                StageEmailAction::ACTION_SOLICITUD_DOCUMENTOS => true,
                StageEmailAction::ACTION_CONFIRMACION_ARCHIVOS => true,
                StageEmailAction::ACTION_APROBACION => true,
                StageEmailAction::ACTION_RECHAZO => true,
                StageEmailAction::ACTION_CORREO_PERSONALIZADO => true,
            ]
        );

        // Licencias (Etapa 2) - No email actions (it's a pass-through stage)
        $this->createStageActions(
            StageEmailAction::STAGE_LICENCIAS,
            [
                StageEmailAction::ACTION_SOLICITUD_DOCUMENTOS => false,
                StageEmailAction::ACTION_CONFIRMACION_ARCHIVOS => false,
                StageEmailAction::ACTION_APROBACION => false,
                StageEmailAction::ACTION_RECHAZO => false,
                StageEmailAction::ACTION_CORREO_PERSONALIZADO => false,
            ]
        );

        // Contabilidad (Etapa 3) - Approval, rejection, and custom email enabled
        $this->createStageActions(
            StageEmailAction::STAGE_CONTABILIDAD,
            [
                StageEmailAction::ACTION_SOLICITUD_DOCUMENTOS => false,
                StageEmailAction::ACTION_CONFIRMACION_ARCHIVOS => false,
                StageEmailAction::ACTION_APROBACION => true,
                StageEmailAction::ACTION_RECHAZO => true,
                StageEmailAction::ACTION_CORREO_PERSONALIZADO => true,
            ]
        );
    }

    /**
     * Create stage email action configurations
     */
    private function createStageActions(string $stage, array $actions): void
    {
        $sortOrder = 0;

        foreach ($actions as $action => $isEnabled) {
            StageEmailAction::create([
                'uid' => Str::uuid()->toString(),
                'validation_stage' => $stage,
                'email_action' => $action,
                'is_enabled' => $isEnabled,
                'sort_order' => $sortOrder++,
            ]);
        }
    }
}
