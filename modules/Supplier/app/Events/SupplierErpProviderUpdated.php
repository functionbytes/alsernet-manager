<?php

namespace Modules\Supplier\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Supplier\Entities\SupplierErpProvider;

/**
 * Evento disparado cuando un proveedor ERP cambia en Supplier
 *
 * Usado por:
 * - SyncProviderToErpListener para sincronizar cambios a Oracle
 *
 * Campos sincronizables:
 * - name, email, phone, website
 * - shipping_cost, discount_percent, partial_delivery_days
 */
class SupplierErpProviderUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SupplierErpProvider $provider,
        public array $changedFields,
        public int $userId,
        public string $ipAddress,
    ) {
    }

    /**
     * Validar que debería sincronizarse a ERP
     *
     * @return bool
     */
    public function shouldSyncToErp(): bool
    {
        // Solo sincronizar si el proveedor tiene ID de ERP
        if (empty($this->provider->erp_provider_id)) {
            return false;
        }

        // Verificar que hay campos sincronizables que cambiaron
        $syncableFields = ['name', 'email', 'phone', 'website', 'shipping_cost', 'discount_percent', 'partial_delivery_days'];
        $hasChangedSyncableField = count(array_intersect($this->changedFields, $syncableFields)) > 0;

        return $hasChangedSyncableField;
    }

    /**
     * Obtener descripción legible del evento para logging
     *
     * @return string
     */
    public function getDescription(): string
    {
        $fields = implode(', ', $this->changedFields);
        return "Proveedor #{$this->provider->id} modificado (campos: $fields)";
    }
}
