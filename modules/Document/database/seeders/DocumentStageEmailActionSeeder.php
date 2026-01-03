<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Document\Entities\DocumentStageEmailAction;

class DocumentStageEmailActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Documentación (Etapa 1) - All email actions enabled
        $this->createStageActions(
            DocumentStageEmailAction::STAGE_DOCUMENTACION,
            [
                DocumentStageEmailAction::ACTION_SOLICITUD_DOCUMENTOS => true,
                DocumentStageEmailAction::ACTION_CONFIRMACION_ARCHIVOS => true,
                DocumentStageEmailAction::ACTION_APROBACION => true,
                DocumentStageEmailAction::ACTION_RECHAZO => true,
                DocumentStageEmailAction::ACTION_CORREO_PERSONALIZADO => true,
            ]
        );

        // Licencias (Etapa 2) - No email actions (it's a pass-through stage)
        $this->createStageActions(
            DocumentStageEmailAction::STAGE_LICENCIAS,
            [
                DocumentStageEmailAction::ACTION_SOLICITUD_DOCUMENTOS => false,
                DocumentStageEmailAction::ACTION_CONFIRMACION_ARCHIVOS => false,
                DocumentStageEmailAction::ACTION_APROBACION => false,
                DocumentStageEmailAction::ACTION_RECHAZO => false,
                DocumentStageEmailAction::ACTION_CORREO_PERSONALIZADO => false,
            ]
        );

        // Contabilidad (Etapa 3) - Approval, rejection, and custom email enabled
        $this->createStageActions(
            DocumentStageEmailAction::STAGE_CONTABILIDAD,
            [
                DocumentStageEmailAction::ACTION_SOLICITUD_DOCUMENTOS => false,
                DocumentStageEmailAction::ACTION_CONFIRMACION_ARCHIVOS => false,
                DocumentStageEmailAction::ACTION_APROBACION => true,
                DocumentStageEmailAction::ACTION_RECHAZO => true,
                DocumentStageEmailAction::ACTION_CORREO_PERSONALIZADO => true,
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
            DocumentStageEmailAction::firstOrCreate(
                [
                    'validation_stage' => $stage,
                    'email_action' => $action,
                ],
                [
                    'uid' => Str::uuid()->toString(),
                    'is_enabled' => $isEnabled,
                    'sort_order' => $sortOrder++,
                ]
            );
        }
    }
}
