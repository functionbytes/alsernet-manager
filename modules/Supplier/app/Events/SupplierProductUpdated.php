<?php

namespace Modules\Supplier\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Supplier\Models\SupplierProduct;

/**
 * Evento disparado cuando un producto cambia en Supplier
 *
 * Usado por:
 * - SyncProductToErpListener para sincronizar cambios a Oracle
 *
 * Campos sincronizables:
 * - name, barcode, recommended_price, is_active
 */
class SupplierProductUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SupplierProduct $product,
        public array $changedFields,
        public int $userId,
        public string $ipAddress,
    ) {}

    /**
     * Validar que debería sincronizarse a ERP
     */
    public function shouldSyncToErp(): bool
    {
        // Solo sincronizar si el producto tiene ID de ERP
        if (empty($this->product->erp_product_id)) {
            return false;
        }

        // Verificar que hay campos sincronizables que cambiaron
        $syncableFields = ['name', 'barcode', 'recommended_price', 'is_active'];
        $hasChangedSyncableField = count(array_intersect($this->changedFields, $syncableFields)) > 0;

        return $hasChangedSyncableField;
    }

    /**
     * Obtener descripción legible del evento para logging
     */
    public function getDescription(): string
    {
        $fields = implode(', ', $this->changedFields);

        return "Producto #{$this->product->id} modificado (campos: $fields)";
    }
}
