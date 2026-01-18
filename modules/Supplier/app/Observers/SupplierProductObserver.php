<?php

namespace Modules\Supplier\Observers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Supplier\Events\SupplierProductUpdated;
use Modules\Supplier\Models\SupplierProduct;

/**
 * Observer que dispara eventos cuando un producto es modificado
 *
 * Similar a SupplierProductPriceObserver pero para productos
 */
class SupplierProductObserver
{
    /**
     * Campos que se pueden sincronizar a ERP
     */
    protected array $syncableFields = [
        'name',
        'barcode',
        'recommended_price',
        'is_active',
    ];

    /**
     * Se llama cuando un producto es actualizado
     */
    public function updated(SupplierProduct $product): void
    {
        // Prevención de loops
        $syncInProgressKey = "sync_in_progress_product_{$product->id}";
        if (Cache::has($syncInProgressKey)) {
            Log::debug('Product update skipped - sync in progress from ERP', [
                'product_id' => $product->id,
            ]);

            return;
        }

        // Detectar qué campos cambiaron
        $dirtyFields = array_keys($product->getDirty());

        // Filtrar solo campos sincronizables
        $changedSyncableFields = array_intersect($dirtyFields, $this->syncableFields);

        // Si no hay campos sincronizables, no hacer nada
        if (empty($changedSyncableFields)) {
            Log::debug('Product update - no syncable fields changed', [
                'product_id' => $product->id,
                'changed_fields' => $dirtyFields,
            ]);

            return;
        }

        Log::info('Product updated - dispatching sync event', [
            'product_id' => $product->id,
            'erp_product_id' => $product->erp_product_id,
            'changed_syncable_fields' => $changedSyncableFields,
        ]);

        // Disparar evento
        SupplierProductUpdated::dispatch(
            $product,
            $changedSyncableFields,
            auth()->id() ?? 1,
            request()?->ip() ?? '127.0.0.1'
        );
    }

    /**
     * Se llama cuando un producto es creado
     */
    public function created(SupplierProduct $product): void
    {
        // No disparar eventos en creación
    }

    /**
     * Se llama cuando un producto es eliminado (soft delete)
     */
    public function deleted(SupplierProduct $product): void
    {
        Log::info('Product soft deleted', [
            'product_id' => $product->id,
            'erp_product_id' => $product->erp_product_id,
        ]);
    }

    /**
     * Se llama cuando un producto es restaurado de soft delete
     */
    public function restored(SupplierProduct $product): void
    {
        Log::info('Product restored from soft delete', [
            'product_id' => $product->id,
            'erp_product_id' => $product->erp_product_id,
        ]);
    }
}
