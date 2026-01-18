<?php

namespace Modules\Supplier\Observers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Supplier\Events\SupplierErpProviderUpdated;
use Modules\Supplier\Models\SupplierErpProvider;

/**
 * Observer que dispara eventos cuando un proveedor ERP es modificado
 *
 * Similar a SupplierProductPriceObserver pero para proveedores
 */
class SupplierErpProviderObserver
{
    /**
     * Campos que se pueden sincronizar a ERP
     */
    protected array $syncableFields = [
        'name',
        'email',
        'phone',
        'website',
        'shipping_cost',
        'discount_percent',
        'partial_delivery_days',
    ];

    /**
     * Se llama cuando un proveedor es actualizado
     */
    public function updated(SupplierErpProvider $provider): void
    {
        // Prevención de loops
        $syncInProgressKey = "sync_in_progress_provider_{$provider->id}";
        if (Cache::has($syncInProgressKey)) {
            Log::debug('Provider update skipped - sync in progress from ERP', [
                'provider_id' => $provider->id,
            ]);

            return;
        }

        // Detectar qué campos cambiaron
        $dirtyFields = array_keys($provider->getDirty());

        // Filtrar solo campos sincronizables
        $changedSyncableFields = array_intersect($dirtyFields, $this->syncableFields);

        // Si no hay campos sincronizables, no hacer nada
        if (empty($changedSyncableFields)) {
            Log::debug('Provider update - no syncable fields changed', [
                'provider_id' => $provider->id,
                'changed_fields' => $dirtyFields,
            ]);

            return;
        }

        Log::info('Provider updated - dispatching sync event', [
            'provider_id' => $provider->id,
            'erp_provider_id' => $provider->erp_provider_id,
            'changed_syncable_fields' => $changedSyncableFields,
        ]);

        // Disparar evento
        SupplierErpProviderUpdated::dispatch(
            $provider,
            $changedSyncableFields,
            auth()->id() ?? 1,
            request()?->ip() ?? '127.0.0.1'
        );
    }

    /**
     * Se llama cuando un proveedor es creado
     */
    public function created(SupplierErpProvider $provider): void
    {
        // No disparar eventos en creación
    }

    /**
     * Se llama cuando un proveedor es eliminado (soft delete)
     */
    public function deleted(SupplierErpProvider $provider): void
    {
        Log::info('Provider soft deleted', [
            'provider_id' => $provider->id,
            'erp_provider_id' => $provider->erp_provider_id,
        ]);
    }

    /**
     * Se llama cuando un proveedor es restaurado de soft delete
     */
    public function restored(SupplierErpProvider $provider): void
    {
        Log::info('Provider restored from soft delete', [
            'provider_id' => $provider->id,
            'erp_provider_id' => $provider->erp_provider_id,
        ]);
    }
}
