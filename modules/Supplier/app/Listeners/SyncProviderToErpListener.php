<?php

namespace Modules\Supplier\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Supplier\Events\SupplierErpProviderUpdated;
use Modules\Supplier\Services\ErpSyncService;
use Modules\Supplier\Entities\SupplierSyncFailure;
use Illuminate\Support\Str;

/**
 * Listener que sincroniza proveedores de Supplier → Oracle en tiempo real
 *
 * Similar a SyncPriceToErpListener pero para proveedores
 */
class SyncProviderToErpListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [5, 15, 30];
    public $queue = 'erp-sync';

    public function __construct(
        protected ErpSyncService $erpSyncService
    ) {
    }

    /**
     * Procesar el evento de cambio de proveedor
     *
     * @param SupplierErpProviderUpdated $event
     * @return void
     * @throws \Exception
     */
    public function handle(SupplierErpProviderUpdated $event): void
    {
        // Validar que se puede sincronizar
        if (!$event->shouldSyncToErp()) {
            Log::info('Provider sync skipped - no erp_provider_id', [
                'supplier_provider_id' => $event->provider->id,
                'changed_fields' => $event->changedFields,
            ]);
            return;
        }

        // Prevención de duplicados
        $cacheKey = $this->getDuplicateKey($event);
        if (Cache::has($cacheKey)) {
            Log::debug('Provider sync skipped - already processing', [
                'supplier_provider_id' => $event->provider->id,
            ]);
            return;
        }

        try {
            // Sincronizar al Oracle
            $this->erpSyncService->syncProviderToOracle(
                $event->provider,
                $event->changedFields
            );

            // Marcar como procesado
            Cache::put($cacheKey, true, now()->addMinutes(5));

            Log::info('Provider synced to ERP successfully', [
                'supplier_provider_id' => $event->provider->id,
                'erp_provider_id' => $event->provider->erp_provider_id,
                'changed_fields' => $event->changedFields,
                'user_id' => $event->userId,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync provider to ERP - will retry', [
                'supplier_provider_id' => $event->provider->id,
                'erp_provider_id' => $event->provider->erp_provider_id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Se llama cuando el job falla permanentemente
     *
     * @param SupplierErpProviderUpdated $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(SupplierErpProviderUpdated $event, \Throwable $exception): void
    {
        // Guardar en Dead Letter Queue
        SupplierSyncFailure::create([
            'uid' => Str::ulid(),
            'sync_type' => 'provider',
            'supplier_id' => $event->provider->id,
            'erp_id' => $event->provider->erp_provider_id,
            'changed_data' => array_combine($event->changedFields, array_fill(0, count($event->changedFields), null)),
            'error_message' => $exception->getMessage(),
            'retry_count' => $this->attempts(),
        ]);

        Log::critical('Provider sync to ERP FAILED PERMANENTLY', [
            'supplier_provider_id' => $event->provider->id,
            'erp_provider_id' => $event->provider->erp_provider_id,
            'error' => $exception->getMessage(),
            'user_id' => $event->userId,
            'changed_fields' => $event->changedFields,
        ]);
    }

    /**
     * Generar clave de cache para prevenir duplicados
     *
     * @param SupplierErpProviderUpdated $event
     * @return string
     */
    protected function getDuplicateKey(SupplierErpProviderUpdated $event): string
    {
        $hashData = json_encode([
            'provider_id' => $event->provider->id,
            'changed_fields' => $event->changedFields,
        ]);

        return 'erp_sync_provider_' . md5($hashData);
    }
}
