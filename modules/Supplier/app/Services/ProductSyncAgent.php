<?php

namespace Modules\Supplier\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Supplier\Models\SupplierProduct;
use Modules\Supplier\Models\SupplierSyncBatch;

/**
 * ProductSyncAgent - Synchronizes SupplierProduct entities with ERP
 *
 * Handles synchronization of product data including:
 * - Product code/reference mapping to ERP
 * - Product status flags and metadata
 * - Pricing information validation
 * - Product hierarchy (groups/categories)
 * - Conflict detection and resolution
 *
 * Sync Strategy:
 * - Fetches unsynced or outdated products
 * - Validates product data before sync
 * - Updates ERP via ErpSyncService
 * - Records sync actions for audit trail
 * - Handles failures with retry capability
 */
class ProductSyncAgent extends BaseSyncAgent
{
    private ErpSyncService $erpSyncService;

    private int $supplierId = 0;

    private ?string $dateRangeStart = null;

    private ?string $dateRangeEnd = null;

    public function __construct(
        SupplierSyncBatch $batch,
        SyncStatusService $syncStatusService,
        ErpSyncService $erpSyncService
    ) {
        parent::__construct($batch, $syncStatusService);
        $this->erpSyncService = $erpSyncService;
    }

    /**
     * Set supplier ID filter for this sync
     */
    public function forSupplier(int $supplierId): self
    {
        $this->supplierId = $supplierId;

        return $this;
    }

    /**
     * Set date range filter for sync
     */
    public function withinDateRange(?string $start, ?string $end): self
    {
        $this->dateRangeStart = $start;
        $this->dateRangeEnd = $end;

        return $this;
    }

    /**
     * Execute the product synchronization
     *
     * Main entry point that orchestrates the full sync lifecycle.
     *
     * @return array{
     *     success: bool,
     *     items_processed: int,
     *     items_failed: int,
     *     items_skipped: int,
     *     message: string
     * }
     */
    public function execute(): array
    {
        try {
            // Fetch items to sync
            $items = $this->fetchItems();

            // Initialize sync with item count
            $itemCount = count($items);
            $this->initializeSync(
                totalItems: $itemCount,
                triggeredBy: $this->batch->triggered_by ?? 'manual'
            );

            if ($itemCount === 0) {
                Log::info('No products to sync', [
                    'supplier_id' => $this->supplierId,
                    'sync_type' => 'product',
                ]);

                return $this->completeSync([
                    'reason' => 'No products matched sync criteria',
                ])->toArray();
            }

            // Process items in batches
            if (! $this->processBatch($items, $this->batch->batch_size)) {
                return $this->failSync(
                    failureReason: 'Product sync was cancelled or encountered critical error',
                    failureCode: 'SYNC_CANCELLED'
                )->toArray();
            }

            // Complete sync successfully
            return $this->completeSync([
                'supplier_id' => $this->supplierId,
                'date_range' => [
                    'start' => $this->dateRangeStart,
                    'end' => $this->dateRangeEnd,
                ],
            ])->toArray();
        } catch (Exception $e) {
            $this->handleError(
                message: 'Critical error in product sync',
                exception: $e,
                context: [
                    'supplier_id' => $this->supplierId,
                    'date_range_start' => $this->dateRangeStart,
                    'date_range_end' => $this->dateRangeEnd,
                ]
            );

            return $this->failSync(
                failureReason: $e->getMessage(),
                failureCode: 'PRODUCT_SYNC_ERROR'
            )->toArray();
        }
    }

    /**
     * Get the sync type identifier
     */
    protected function getSyncType(): string
    {
        return 'product';
    }

    /**
     * Fetch all products that need synchronization
     *
     * Returns an iterable collection of SupplierProduct models that are either:
     * - Never synced before (last_synced_at is null)
     * - Modified after last sync (erp_updated_at > last_synced_at)
     *
     * Eagerly loads relationships to prevent N+1 queries.
     *
     * @return iterable<SupplierProduct>
     */
    protected function fetchItems(): iterable
    {
        try {
            $query = SupplierProduct::query()
                ->with(['group', 'supplier'])
                ->where('is_active', true);

            // Filter by supplier if specified
            if ($this->supplierId > 0) {
                $query->where('supplier_id', $this->supplierId);
            }

            // Filter by date range if specified
            if ($this->dateRangeStart) {
                $query->where('updated_at', '>=', $this->dateRangeStart);
            }

            if ($this->dateRangeEnd) {
                $query->where('updated_at', '<=', $this->dateRangeEnd);
            }

            // Filter only products that need sync
            $query->where(function ($q) {
                $q->whereNull('last_synced_at')
                    ->orWhereRaw('erp_updated_at > last_synced_at');
            });

            // Order by priority: newer changes first
            $products = $query->orderBy('erp_updated_at', 'desc')
                ->orderBy('id', 'asc')
                ->cursor();

            Log::debug('Fetched products for sync', [
                'supplier_id' => $this->supplierId,
                'batch_id' => $this->batch->id,
            ]);

            return $products;
        } catch (Exception $e) {
            Log::error('Failed to fetch products for sync', [
                'supplier_id' => $this->supplierId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Process a single product for synchronization
     *
     * Validates product data, syncs to ERP, and records the action.
     *
     * @param  SupplierProduct  $product  The product to sync
     * @return bool True if sync succeeded, false otherwise
     */
    protected function processItem(mixed $product): bool
    {
        $startTime = microtime(true);

        try {
            // Cast to ensure type safety
            if (! $product instanceof SupplierProduct) {
                throw new Exception('Invalid item type: expected SupplierProduct');
            }

            // Skip if product has no ERP mapping
            if (! $product->erp_product_id) {
                $this->skipItem('Product has no ERP ID mapping');

                $this->logAction(
                    entityType: 'product',
                    entityId: $product->id,
                    action: 'sync',
                    result: 'skipped',
                    message: 'No ERP ID mapping found'
                );

                return false;
            }

            // Validate product data
            if (! $this->validateProduct($product)) {
                throw new Exception('Product validation failed');
            }

            // Detect changes from last sync
            $changedFields = $this->detectChanges($product);

            if (empty($changedFields)) {
                $this->skipItem('No changes detected since last sync');

                $this->logAction(
                    entityType: 'product',
                    entityId: $product->id,
                    action: 'sync',
                    result: 'skipped',
                    message: 'No changes since last sync'
                );

                return false;
            }

            // Sync to ERP
            $this->erpSyncService->syncProductToOracle($product, $changedFields);

            // Update sync timestamp
            $product->update(['last_synced_at' => now()]);

            // Log successful action
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->logAction(
                entityType: 'product',
                entityId: $product->id,
                action: 'sync',
                result: 'success',
                message: "Product synced successfully: {$product->name}",
                changes: $changedFields,
                durationMs: $durationMs
            );

            Log::debug('Product synced successfully', [
                'product_id' => $product->id,
                'erp_id' => $product->erp_product_id,
                'code' => $product->code,
                'changed_fields' => $changedFields,
                'duration_ms' => $durationMs,
            ]);

            return true;
        } catch (Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            Log::warning('Failed to sync product', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ]);

            // Record failure for retry processing
            $this->recordFailure(
                syncType: 'product',
                supplierId: $product->supplier_id ?? 0,
                entityId: $product->id,
                erpId: $product->erp_product_id,
                changedData: $this->detectChanges($product),
                errorMessage: $e->getMessage(),
                errorCode: 'PRODUCT_SYNC_FAILED',
                context: [
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                ],
                maxRetries: 3
            );

            // Log failure action
            $this->logAction(
                entityType: 'product',
                entityId: $product->id,
                action: 'sync',
                result: 'failed',
                message: "Failed to sync product: {$product->name}",
                errorCode: 'PRODUCT_SYNC_FAILED',
                errorMessage: $e->getMessage(),
                durationMs: $durationMs
            );

            return false;
        }
    }

    /**
     * Validate product data before sync
     *
     * Checks required fields and data consistency.
     */
    private function validateProduct(SupplierProduct $product): bool
    {
        // Check required fields
        if (! $product->name || ! $product->code) {
            Log::warning('Product missing required fields', [
                'product_id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
            ]);

            return false;
        }

        // Validate supplier relationship
        if (! $product->supplier_id) {
            Log::warning('Product has no supplier', [
                'product_id' => $product->id,
            ]);

            return false;
        }

        // Validate pricing data
        if ($product->average_cost !== null && $product->average_cost < 0) {
            Log::warning('Product has negative cost', [
                'product_id' => $product->id,
                'cost' => $product->average_cost,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Detect which fields have changed since last sync
     *
     * Compares current product data with ERP's last known state
     * to identify what needs to be synced.
     *
     * @return array List of field names that changed
     */
    private function detectChanges(SupplierProduct $product): array
    {
        $changedFields = [];

        // Check basic fields
        if ($product->isDirty('name')) {
            $changedFields[] = 'name';
        }

        if ($product->isDirty('code')) {
            $changedFields[] = 'code';
        }

        if ($product->isDirty('barcode')) {
            $changedFields[] = 'barcode';
        }

        if ($product->isDirty('reference')) {
            $changedFields[] = 'reference';
        }

        if ($product->isDirty('average_cost')) {
            $changedFields[] = 'average_cost';
        }

        if ($product->isDirty('last_purchase_cost')) {
            $changedFields[] = 'last_purchase_cost';
        }

        if ($product->isDirty('recommended_price')) {
            $changedFields[] = 'recommended_price';
        }

        // Check dimensions
        if ($product->isDirty('length') || $product->isDirty('width')
            || $product->isDirty('height') || $product->isDirty('weight')) {
            $changedFields[] = 'dimensions';
        }

        // Check status flags
        if ($product->isDirty('is_active')) {
            $changedFields[] = 'is_active';
        }

        if ($product->isDirty('brand_name')) {
            $changedFields[] = 'brand_name';
        }

        if ($product->isDirty('model_name')) {
            $changedFields[] = 'model_name';
        }

        return array_unique($changedFields);
    }
}
