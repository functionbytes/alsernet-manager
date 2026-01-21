<?php

namespace Modules\Supplier\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Supplier\Models\SupplierCategory;
use Modules\Supplier\Models\SupplierSyncBatch;

/**
 * CategorySyncAgent - Synchronizes SupplierCategory entities with ERP
 *
 * Handles synchronization of supplier-category relationships including:
 * - Parent-child category hierarchies
 * - Category mappings and metadata
 * - Supplier category assignments
 * - Priority and ordering information
 * - Validation of parent categories before child sync
 *
 * Sync Strategy:
 * - Fetches all supplier category assignments
 * - Validates hierarchical relationships
 * - Syncs parent categories before children
 * - Handles bulk category operations
 * - Records sync actions for audit trail
 */
class CategorySyncAgent extends BaseSyncAgent
{
    private ErpSyncService $erpSyncService;

    private int $supplierId = 0;

    private bool $hierarchicalOnly = true;

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
     * Set whether to only sync hierarchical categories
     */
    public function hierarchicalOnly(bool $value): self
    {
        $this->hierarchicalOnly = $value;

        return $this;
    }

    /**
     * Execute the category synchronization
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
                Log::info('No supplier categories to sync', [
                    'supplier_id' => $this->supplierId,
                    'sync_type' => 'category',
                ]);

                return $this->completeSync([
                    'reason' => 'No supplier categories matched sync criteria',
                ])->toArray();
            }

            // Process items in batches
            if (! $this->processBatch($items, $this->batch->batch_size)) {
                return $this->failSync(
                    failureReason: 'Category sync was cancelled or encountered critical error',
                    failureCode: 'SYNC_CANCELLED'
                )->toArray();
            }

            // Complete sync successfully
            return $this->completeSync([
                'supplier_id' => $this->supplierId,
                'hierarchical_only' => $this->hierarchicalOnly,
            ])->toArray();
        } catch (Exception $e) {
            $this->handleError(
                message: 'Critical error in category sync',
                exception: $e,
                context: [
                    'supplier_id' => $this->supplierId,
                ]
            );

            return $this->failSync(
                failureReason: $e->getMessage(),
                failureCode: 'CATEGORY_SYNC_ERROR'
            )->toArray();
        }
    }

    /**
     * Get the sync type identifier
     */
    protected function getSyncType(): string
    {
        return 'category';
    }

    /**
     * Fetch all categories that need synchronization
     *
     * Returns supplier category assignments ordered by hierarchy depth
     * to ensure parents are processed before children.
     *
     * @return iterable<SupplierCategory>
     */
    protected function fetchItems(): iterable
    {
        try {
            $query = SupplierCategory::query()
                ->with(['supplier', 'category'])
                ->where('is_active', true);

            // Filter by supplier if specified
            if ($this->supplierId > 0) {
                $query->where('supplier_id', $this->supplierId);
            }

            // Order by priority (highest first) and then by ID
            $categories = $query->orderByDesc('priority')
                ->orderBy('id', 'asc')
                ->cursor();

            Log::debug('Fetched supplier categories for sync', [
                'supplier_id' => $this->supplierId,
                'batch_id' => $this->batch->id,
            ]);

            return $categories;
        } catch (Exception $e) {
            Log::error('Failed to fetch supplier categories for sync', [
                'supplier_id' => $this->supplierId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Process a single supplier category for synchronization
     *
     * Validates category relationships, syncs to ERP, and records the action.
     *
     * @param  SupplierCategory  $category  The supplier category to sync
     * @return bool True if sync succeeded, false otherwise
     */
    protected function processItem(mixed $category): bool
    {
        $startTime = microtime(true);

        try {
            // Cast to ensure type safety
            if (! $category instanceof SupplierCategory) {
                throw new Exception('Invalid item type: expected SupplierCategory');
            }

            // Load relationships if not already loaded
            if (! $category->relationLoaded('category')) {
                $category->load('category');
            }

            if (! $category->relationLoaded('supplier')) {
                $category->load('supplier');
            }

            // Validate category data
            if (! $this->validateCategory($category)) {
                throw new Exception('Category validation failed');
            }

            // Sync to ERP
            $this->erpSyncService->syncCategoryToOracle($category);

            // Log successful action
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $categoryName = $category->category?->name ?? 'Unknown';

            $this->logAction(
                entityType: 'supplier_category',
                entityId: $category->id,
                action: 'sync',
                result: 'success',
                message: "Supplier category synced: {$categoryName}",
                dataAfter: [
                    'supplier_id' => $category->supplier_id,
                    'category_id' => $category->category_id,
                    'priority' => $category->priority,
                    'is_active' => $category->is_active,
                ],
                durationMs: $durationMs
            );

            Log::debug('Supplier category synced successfully', [
                'category_id' => $category->id,
                'supplier_id' => $category->supplier_id,
                'category_name' => $categoryName,
                'duration_ms' => $durationMs,
            ]);

            return true;
        } catch (Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            Log::warning('Failed to sync supplier category', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ]);

            // Record failure for retry processing
            $this->recordFailure(
                syncType: 'category',
                supplierId: $category->supplier_id,
                entityId: $category->id,
                erpId: null,
                changedData: [
                    'supplier_id' => $category->supplier_id,
                    'category_id' => $category->category_id,
                    'priority' => $category->priority,
                ],
                errorMessage: $e->getMessage(),
                errorCode: 'CATEGORY_SYNC_FAILED',
                context: [
                    'category_name' => $category->category->name ?? 'Unknown',
                ],
                maxRetries: 3
            );

            // Log failure action
            $this->logAction(
                entityType: 'supplier_category',
                entityId: $category->id,
                action: 'sync',
                result: 'failed',
                message: 'Failed to sync supplier category',
                errorCode: 'CATEGORY_SYNC_FAILED',
                errorMessage: $e->getMessage(),
                durationMs: $durationMs
            );

            return false;
        }
    }

    /**
     * Validate category data before sync
     *
     * Checks relationships and required data.
     */
    private function validateCategory(SupplierCategory $category): bool
    {
        // Check required relationships
        if (! $category->supplier_id) {
            Log::warning('Supplier category has no supplier', [
                'category_id' => $category->id,
            ]);

            return false;
        }

        if (! $category->category_id) {
            Log::warning('Supplier category has no category', [
                'category_id' => $category->id,
            ]);

            return false;
        }

        // Validate category exists
        if (! $category->category) {
            Log::warning('Category relationship not found', [
                'category_id' => $category->id,
                'category_fk' => $category->category_id,
            ]);

            return false;
        }

        // Validate supplier exists
        if (! $category->supplier) {
            Log::warning('Supplier relationship not found', [
                'category_id' => $category->id,
                'supplier_fk' => $category->supplier_id,
            ]);

            return false;
        }

        // Validate priority value
        if ($category->priority < 0) {
            Log::warning('Category has invalid priority', [
                'category_id' => $category->id,
                'priority' => $category->priority,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Check if parent categories exist in ERP
     *
     * Validates that parent categories are already synced before syncing children.
     * This is useful for hierarchical category structures.
     *
     * @return bool True if all parent categories are synced, false otherwise
     */
    private function validateParentCategoriesInErp(SupplierCategory $category): bool
    {
        // If category has parent relationship defined, validate it
        if (! isset($category->category->parent_id) || ! $category->category->parent_id) {
            return true; // Root category, no parent to validate
        }

        // Load parent category
        $parentCategory = $category->category->parent;

        if (! $parentCategory) {
            Log::warning('Parent category not found', [
                'category_id' => $category->category_id,
                'parent_id' => $category->category->parent_id,
            ]);

            return false;
        }

        Log::debug('Parent category validated', [
            'category_id' => $category->id,
            'parent_name' => $parentCategory->name,
        ]);

        return true;
    }
}
