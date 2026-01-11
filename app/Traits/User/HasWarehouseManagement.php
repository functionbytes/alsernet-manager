<?php

namespace App\Traits\User;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Trait HasWarehouseManagement
 *
 * Gestiona las relaciones y operaciones relacionadas con almacenes (warehouses)
 * para el modelo User.
 *
 * @package App\Traits\User
 */
trait HasWarehouseManagement
{
    /**
     * Relación many-to-many con Warehouses
     * Un usuario puede estar asignado a múltiples almacenes
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(
            'App\Models\Warehouse\Warehouse',
            'user_warehouse',
            'user_id',
            'warehouse_id'
        )->withPivot('is_default', 'can_transfer', 'can_inventory')
            ->withTimestamps();
    }

    /**
     * Obtener el almacén predeterminado del usuario
     */
    public function defaultWarehouse()
    {
        return $this->warehouses()
            ->where('user_warehouse.is_default', true)
            ->first();
    }

    /**
     * Obtener almacenes donde el usuario puede realizar inventarios
     */
    public function inventoryWarehouses()
    {
        return $this->warehouses()
            ->where('user_warehouse.can_inventory', true);
    }

    /**
     * Obtener almacenes donde el usuario puede transferir productos
     */
    public function transferWarehouses()
    {
        return $this->warehouses()
            ->where('user_warehouse.can_transfer', true);
    }

    /**
     * Asignar un almacén al usuario
     */
    public function assignWarehouse($warehouseId, $isDefault = false, $canTransfer = true, $canInventory = true)
    {
        // Si es predeterminado, quitar el estado de otros
        if ($isDefault) {
            $this->warehouses()->update(['user_warehouse.is_default' => false]);
        }

        // Insertar o actualizar la relación
        $this->warehouses()->syncWithoutDetaching([
            $warehouseId => [
                'is_default' => $isDefault,
                'can_transfer' => $canTransfer,
                'can_inventory' => $canInventory,
            ],
        ]);
    }

    /**
     * Desasignar un almacén
     */
    public function removeWarehouse($warehouseId)
    {
        $this->warehouses()->detach($warehouseId);
    }

    /**
     * Verificar si el usuario tiene acceso a un almacén
     */
    public function hasAccessToWarehouse($warehouseId): bool
    {
        return $this->warehouses()
            ->where('warehouse_id', $warehouseId)
            ->exists();
    }

    /**
     * Verificar si puede realizar inventario en un almacén
     */
    public function canPerformInventory($warehouseId): bool
    {
        return $this->warehouses()
            ->where('warehouse_id', $warehouseId)
            ->where('user_warehouse.can_inventory', true)
            ->exists();
    }

    /**
     * Verificar si puede transferir productos en un almacén
     */
    public function canTransferInWarehouse($warehouseId): bool
    {
        return $this->warehouses()
            ->where('warehouse_id', $warehouseId)
            ->where('user_warehouse.can_transfer', true)
            ->exists();
    }
}
