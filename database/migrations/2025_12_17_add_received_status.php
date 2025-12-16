<?php

use App\Models\Document\DocumentStatus;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Restructures document statuses to align with email notification flow:
     * - Removes "Completed" (redundant with "Approved")
     * - Adds "Received" (documents uploaded, awaiting review)
     * - Updates status order and descriptions
     */
    public function up(): void
    {
        // Add the new "received" status
        DocumentStatus::firstOrCreate(
            ['key' => 'received'],
            [
                'label' => 'Documentos Recibidos',
                'description' => 'Documentos recibidos del cliente, en espera de revisión',
                'color' => '#0dcaf0',
                'icon' => 'inbox',
                'is_active' => true,
                'order' => 3,
            ]
        );

        // Deactivate "completed" status (no longer used - "approved" is the final state)
        $completed = DocumentStatus::where('key', 'completed')->first();
        if ($completed) {
            $completed->update([
                'is_active' => false,
                'label' => 'Completado (Obsoleto)',
                'description' => 'Status obsoleto - use "Approved" instead',
            ]);
        }

        // Update order of existing statuses to accommodate "received"
        // pending=1, awaiting_documents=2, received=3, incomplete=4, approved=5, rejected=6, cancelled=7
        $statusOrders = [
            'pending' => 1,
            'awaiting_documents' => 2,
            'received' => 3,
            'incomplete' => 4,
            'approved' => 5,
            'rejected' => 6,
            'cancelled' => 7,
        ];

        foreach ($statusOrders as $key => $order) {
            DocumentStatus::where('key', $key)->update(['order' => $order]);
        }

        // Update descriptions for clarity
        DocumentStatus::where('key', 'pending')->update([
            'label' => 'Solicitado',
            'description' => 'Documentación solicitada, en espera de que el cliente envíe los documentos',
        ]);

        DocumentStatus::where('key', 'approved')->update([
            'description' => 'Documentos verificados y aprobados. Solicitud completada.',
        ]);

        DocumentStatus::where('key', 'rejected')->update([
            'description' => 'Documentos rechazados. Cliente debe reenviar documentación.',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the "received" status
        DocumentStatus::where('key', 'received')->delete();

        // Reactivate "completed" status
        DocumentStatus::where('key', 'completed')->update(['is_active' => true]);

        // Revert order
        $statusOrders = [
            'pending' => 1,
            'incomplete' => 2,
            'awaiting_documents' => 3,
            'approved' => 4,
            'completed' => 5,
            'rejected' => 6,
            'cancelled' => 7,
        ];

        foreach ($statusOrders as $key => $order) {
            DocumentStatus::where('key', $key)->update(['order' => $order]);
        }

        // Revert descriptions
        DocumentStatus::where('key', 'pending')->update([
            'label' => 'Pendiente',
            'description' => 'Documento recién creado, en espera de procesamiento',
        ]);
    }
};
