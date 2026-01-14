<?php

namespace Modules\Supplier\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Supplier\Events\SupplierProductUpdated;
use Modules\Supplier\Services\ErpSyncService;
use Modules\Supplier\Entities\SupplierSyncFailure;
use Illuminate\Support\Str;

/**
 * Listener que sincroniza productos de Supplier → Oracle en tiempo real
 *
 * Similar a SyncPriceToErpListener pero para productos
 */
class SyncProductToErpListener implements ShouldQueue
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
     * Procesar el evento de cambio de producto
     *
     * @param SupplierProductUpdated $event
     * @return void
     * @throws \Exception
     */
    public function handle(SupplierProductUpdated $event): void
    {
        // Validar que se puede sincronizar
        if (!$event->shouldSyncToErp()) {
            Log::info('Product sync skipped - no erp_product_id', [
                'supplier_product_id' => $event->product->id,
                'changed_fields' => $event->changedFields,
            ]);
            return;
        }

        // Prevención de duplicados
        $cacheKey = $this->getDuplicateKey($event);
        if (Cache::has($cacheKey)) {
            Log::debug('Product sync skipped - already processing', [
                'supplier_product_id' => $event->product->id,
            ]);
            return;
        }

        try {
            // Sincronizar al Oracle
            $this->erpSyncService->syncProductToOracle(
                $event->product,
                $event->changedFields
            );

            // Marcar como procesado
            Cache::put($cacheKey, true, now()->addMinutes(5));

            Log::info('Product synced to ERP successfully', [
                'supplier_product_id' => $event->product->id,
                'erp_product_id' => $event->product->erp_product_id,
                'changed_fields' => $event->changedFields,
                'user_id' => $event->userId,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync product to ERP - will retry', [
                'supplier_product_id' => $event->product->id,
                'erp_product_id' => $event->product->erp_product_id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Se llama cuando el job falla permanentemente
     *
     * @param SupplierProductUpdated $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed(SupplierProductUpdated $event, \Throwable $exception): void
    {
        // Guardar en Dead Letter Queue
        SupplierSyncFailure::create([
            'uid' => Str::ulid(),
            'sync_type' => 'product',
            'supplier_id' => $event->product->id,
            'erp_id' => $event->product->erp_product_id,
            'changed_data' => array_combine($event->changedFields, array_fill(0, count($event->changedFields), null)),
            'error_message' => $exception->getMessage(),
            'retry_count' => $this->attempts(),
        ]);

        Log::critical('Product sync to ERP FAILED PERMANENTLY', [
            'supplier_product_id' => $event->product->id,
            'erp_product_id' => $event->product->erp_product_id,
            'error' => $exception->getMessage(),
            'user_id' => $event->userId,
            'changed_fields' => $event->changedFields,
        ]);
    }

    /**
     * Generar clave de cache para prevenir duplicados
     *
     * @param SupplierProductUpdated $event
     * @return string
     */
    protected function getDuplicateKey(SupplierProductUpdated $event): string
    {
        $hashData = json_encode([
            'product_id' => $event->product->id,
            'changed_fields' => $event->changedFields,
        ]);

        return 'erp_sync_product_' . md5($hashData);
    }
}
