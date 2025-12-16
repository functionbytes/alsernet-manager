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
        $statuses = [
            [
                'key' => 'pending',
                'label' => 'Pendiente',
                'description' => 'Documento recién creado, en espera de procesamiento',
                'color' => '#6c757d',
                'icon' => 'circle',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'incomplete',
                'label' => 'Incompleto',
                'description' => 'Faltan documentos requeridos',
                'color' => '#ffc107',
                'icon' => 'alert-circle',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'awaiting_documents',
                'label' => 'Esperando Documentos',
                'description' => 'En espera de que el cliente envíe los documentos requeridos',
                'color' => '#17a2b8',
                'icon' => 'hourglass',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'key' => 'approved',
                'label' => 'Aprobado',
                'description' => 'Documentos verificados y aprobados',
                'color' => '#28a745',
                'icon' => 'check-circle',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'key' => 'completed',
                'label' => 'Completado',
                'description' => 'Documento procesado completamente',
                'color' => '#20c997',
                'icon' => 'badge-check',
                'is_active' => true,
                'order' => 5,
            ],
            [
                'key' => 'rejected',
                'label' => 'Rechazado',
                'description' => 'Documento rechazado por validación fallida',
                'color' => '#dc3545',
                'icon' => 'x-circle',
                'is_active' => true,
                'order' => 6,
            ],
            [
                'key' => 'cancelled',
                'label' => 'Cancelado',
                'description' => 'Solicitud de documento cancelada',
                'color' => '#6c757d',
                'icon' => 'ban',
                'is_active' => true,
                'order' => 7,
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
