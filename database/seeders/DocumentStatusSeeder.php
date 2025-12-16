<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DocumentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Document Status Flow (aligned with email notifications):
        // 1. Solicitado (Pending) → Initial Request email sent
        // 2. Esperando Documentos → Reminder emails sent periodically
        // 3. Documentos Recibidos (NEW) → Upload Confirmation email sent
        // 4. Incompleto → Missing documents (intermediate state)
        // 5. Aprobado (Final) → Approval email sent (COMPLETION)
        // 6. Rechazado → Rejection email sent (awaiting resubmission)
        // 7. Cancelado → Request cancelled

        $statuses = [
            [
                'key' => 'pending',
                'label' => 'Solicitado',
                'description' => 'Documentación solicitada. Email de solicitud enviado. Esperando que el cliente envíe documentos.',
                'color' => '#6c757d',
                'icon' => 'file-text',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'awaiting_documents',
                'label' => 'Esperando Documentos',
                'description' => 'Cliente no ha enviado documentos. Recordatorios enviados periódicamente.',
                'color' => '#17a2b8',
                'icon' => 'hourglass',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'received',
                'label' => 'Documentos Recibidos',
                'description' => 'Documentos recibidos del cliente. Email de confirmación enviado. En espera de revisión del administrador.',
                'color' => '#0dcaf0',
                'icon' => 'inbox',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'key' => 'incomplete',
                'label' => 'Incompleto',
                'description' => 'Faltan documentos requeridos después de la revisión del administrador.',
                'color' => '#ffc107',
                'icon' => 'alert-circle',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'key' => 'approved',
                'label' => 'Aprobado',
                'description' => 'Documentos verificados y aprobados. Email de aprobación enviado. Solicitud completada.',
                'color' => '#28a745',
                'icon' => 'check-circle',
                'is_active' => true,
                'order' => 5,
            ],
            [
                'key' => 'rejected',
                'label' => 'Rechazado',
                'description' => 'Documentos rechazados con motivo. Email de rechazo enviado. Cliente debe reenviar documentación.',
                'color' => '#dc3545',
                'icon' => 'x-circle',
                'is_active' => true,
                'order' => 6,
            ],
            [
                'key' => 'cancelled',
                'label' => 'Cancelado',
                'description' => 'Solicitud de documento cancelada por el administrador.',
                'color' => '#6c757d',
                'icon' => 'ban',
                'is_active' => true,
                'order' => 7,
            ],
            // Mark "completed" as inactive (no longer used - "approved" is the final state)
            [
                'key' => 'completed',
                'label' => 'Completado (Obsoleto)',
                'description' => 'Status obsoleto. Use "Approved" en su lugar.',
                'color' => '#20c997',
                'icon' => 'badge-check',
                'is_active' => false,
                'order' => 8,
            ],
        ];

        foreach ($statuses as $status) {
            \App\Models\Document\DocumentStatus::firstOrCreate(
                ['key' => $status['key']],
                $status
            );
        }
    }
}
