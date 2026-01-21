<?php

namespace Modules\Supplier\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Modules\Supplier\Models\SupplierSyncBatch;

/**
 * Orchestrator agent that coordinates multiple sync agents
 *
 * Spawns and manages concurrent sync operations, monitors progress,
 * handles failures and retries, and aggregates results.
 *
 * Supports:
 * - Syncing by type (product, category, price, provider)
 * - Supplier-specific syncs
 * - Parallel processing of multiple agents
 * - Progress monitoring and real-time updates
 * - Batch failure and retry handling
 */
class SyncCoordinatorAgent
{
    private array $activeAgents = [];

    private array $agentResults = [];

    private SupplierSyncBatch $coordinatorBatch;

    public function __construct(private SyncStatusService $syncStatusService) {}

    /**
     * Coordinate a synchronization operation by type
     *
     * Creates a batch and spawns appropriate sync agents based on sync type.
     *
     * @param  string  $syncType  Type: 'product', 'category', 'price', 'provider', 'all'
     * @param  int|null  $supplierId  Optional supplier ID for supplier-specific sync
     * @param  string  $triggeredBy  Trigger source: 'manual', 'scheduled', 'webhook', 'api'
     * @param  array|null  $filterCriteria  Optional filter criteria
     * @return array{
     *     success: bool,
     *     message: string,
     *     batch_id: int|null,
     *     sync_types: array,
     *     agents_started: int,
     *     total_items: int|null
     * }
     */
    public function coordinateSync(
        string $syncType,
        ?int $supplierId = null,
        string $triggeredBy = 'manual',
        ?array $filterCriteria = null
    ): array {
        try {
            Log::info('Coordinator sync started', [
                'sync_type' => $syncType,
                'supplier_id' => $supplierId,
                'triggered_by' => $triggeredBy,
            ]);

            // Create coordinator batch
            $this->coordinatorBatch = $this->createCoordinatorBatch(
                syncType: $syncType,
                supplierId: $supplierId,
                triggeredBy: $triggeredBy,
                filterCriteria: $filterCriteria
            );

            // Determine which agents to spawn
            $syncTypes = $this->determineSyncTypes($syncType);

            // Spawn agents in parallel
            $totalItems = $this->runAgentsInParallel($syncTypes, $supplierId, $filterCriteria);

            // Monitor progress
            $this->monitorProgress();

            // Handle batch completion
            return $this->handleBatchCompletion(
                batchId: $this->coordinatorBatch->id,
                syncTypes: $syncTypes,
                agentsStarted: count($this->activeAgents),
                totalItems: $totalItems
            );
        } catch (Exception $e) {
            Log::error('Coordinator sync failed', [
                'error' => $e->getMessage(),
                'batch_id' => $this->coordinatorBatch->id ?? null,
            ]);

            if (isset($this->coordinatorBatch)) {
                $this->coordinatorBatch->markAsFailed();
            }

            return [
                'success' => false,
                'message' => 'Coordinator sync failed: '.$e->getMessage(),
                'batch_id' => $this->coordinatorBatch->id ?? null,
                'sync_types' => [],
                'agents_started' => 0,
                'total_items' => 0,
            ];
        }
    }

    /**
     * Create a coordinator batch record
     */
    private function createCoordinatorBatch(
        string $syncType,
        ?int $supplierId,
        string $triggeredBy,
        ?array $filterCriteria
    ): SupplierSyncBatch {
        return SupplierSyncBatch::create([
            'supplier_id' => $supplierId,
            'batch_name' => "Coordinator: {$syncType} sync",
            'sync_type' => $syncType,
            'status' => 'pending',
            'priority' => 'normal',
            'batch_size' => 100,
            'total_batches' => 0,
            'processed_batches' => 0,
            'total_items' => 0,
            'processed_items' => 0,
            'failed_items' => 0,
            'retry_attempt' => 0,
            'max_retries' => 3,
            'triggered_by' => $triggeredBy,
            'filter_criteria' => $filterCriteria,
        ]);
    }

    /**
     * Determine which sync types to execute
     *
     * Translates 'all' to multiple sync types or returns single type.
     */
    private function determineSyncTypes(string $syncType): array
    {
        return match ($syncType) {
            'all' => ['product', 'category', 'price', 'provider'],
            'products' => ['product'],
            'categories' => ['category'],
            'prices' => ['price'],
            'providers' => ['provider'],
            default => [$syncType],
        };
    }

    /**
     * Run sync agents in parallel
     *
     * Spawns sync agents as queue jobs for parallel processing.
     *
     * @return int Total items across all agents
     */
    private function runAgentsInParallel(
        array $syncTypes,
        ?int $supplierId,
        ?array $filterCriteria
    ): int {
        $totalItems = 0;

        foreach ($syncTypes as $syncType) {
            try {
                // Create batch for this sync type
                $batch = SupplierSyncBatch::create([
                    'supplier_id' => $supplierId,
                    'batch_name' => "Sync: {$syncType}",
                    'sync_type' => $syncType,
                    'status' => 'pending',
                    'priority' => 'normal',
                    'batch_size' => 100,
                    'total_batches' => 0,
                    'processed_batches' => 0,
                    'total_items' => 0,
                    'processed_items' => 0,
                    'failed_items' => 0,
                    'retry_attempt' => 0,
                    'max_retries' => 3,
                    'triggered_by' => 'coordinator',
                    'filter_criteria' => $filterCriteria,
                ]);

                // Dispatch sync job (this will be created per agent type)
                $jobClass = $this->getSyncJobClass($syncType);
                Queue::push(new $jobClass($batch, $this->syncStatusService));

                $this->activeAgents[$syncType] = [
                    'batch_id' => $batch->id,
                    'status' => 'dispatched',
                    'created_at' => now(),
                ];

                Log::info('Sync agent dispatched', [
                    'sync_type' => $syncType,
                    'batch_id' => $batch->id,
                    'supplier_id' => $supplierId,
                ]);

                // Count items to estimate total (approximate)
                $totalItems += $this->estimateTotalItems($syncType, $supplierId, $filterCriteria);
            } catch (Exception $e) {
                Log::error('Failed to dispatch sync agent', [
                    'sync_type' => $syncType,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $totalItems;
    }

    /**
     * Get the job class for a specific sync type
     *
     * Maps sync type to corresponding job class.
     * These jobs should be created as concrete implementations.
     */
    private function getSyncJobClass(string $syncType): string
    {
        return match ($syncType) {
            'product' => \Modules\Supplier\Jobs\SyncProductsJob::class,
            'category' => \Modules\Supplier\Jobs\SyncCategoriesJob::class,
            'price' => \Modules\Supplier\Jobs\SyncPricesJob::class,
            'provider' => \Modules\Supplier\Jobs\SyncProvidersJob::class,
            default => throw new Exception("Unknown sync type: {$syncType}"),
        };
    }

    /**
     * Estimate total items to be synced
     *
     * Provides rough estimate for progress calculation.
     */
    private function estimateTotalItems(
        string $syncType,
        ?int $supplierId,
        ?array $filterCriteria
    ): int {
        try {
            // This is a placeholder - actual implementation depends on your models
            // and should count entities to be synced
            return match ($syncType) {
                'product' => \Modules\Supplier\Models\SupplierProduct::query()
                    ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                    ->when($filterCriteria, fn ($q) => $this->applyFilters($q, $filterCriteria))
                    ->count(),
                'category' => \Modules\Supplier\Models\SupplierCategory::query()
                    ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                    ->when($filterCriteria, fn ($q) => $this->applyFilters($q, $filterCriteria))
                    ->count(),
                'price' => \Modules\Supplier\Models\SupplierProductPrice::query()
                    ->when($supplierId, fn ($q) => $q->whereHas('product', fn ($q) => $q->where('supplier_id', $supplierId)))
                    ->when($filterCriteria, fn ($q) => $this->applyFilters($q, $filterCriteria))
                    ->count(),
                'provider' => \Modules\Supplier\Models\SupplierErpProvider::query()
                    ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                    ->when($filterCriteria, fn ($q) => $this->applyFilters($q, $filterCriteria))
                    ->count(),
                default => 0,
            };
        } catch (Exception $e) {
            Log::warning('Failed to estimate items', [
                'sync_type' => $syncType,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Apply filter criteria to query
     *
     * @return $query
     */
    private function applyFilters($query, array $filterCriteria)
    {
        if (isset($filterCriteria['category_id'])) {
            $query->where('category_id', $filterCriteria['category_id']);
        }

        if (isset($filterCriteria['updated_since'])) {
            $query->where('updated_at', '>=', $filterCriteria['updated_since']);
        }

        if (isset($filterCriteria['status'])) {
            $query->where('status', $filterCriteria['status']);
        }

        return $query;
    }

    /**
     * Monitor progress of running agents
     *
     * Polls status of all active agents and broadcasts updates.
     */
    private function monitorProgress(): void
    {
        // This would typically run in a loop or be handled by queue monitoring
        // For now, we log the active agents
        foreach ($this->activeAgents as $syncType => $agentInfo) {
            Log::debug('Monitoring sync agent', [
                'sync_type' => $syncType,
                'batch_id' => $agentInfo['batch_id'],
                'status' => $agentInfo['status'],
            ]);
        }
    }

    /**
     * Handle batch completion
     *
     * Aggregates results from all agents and returns summary.
     */
    private function handleBatchCompletion(
        int $batchId,
        array $syncTypes,
        int $agentsStarted,
        int $totalItems
    ): array {
        $this->coordinatorBatch->update([
            'total_items' => $totalItems,
            'status' => 'running',
        ]);

        $this->coordinatorBatch->markAsStarted();

        Log::info('Batch processing started', [
            'batch_id' => $batchId,
            'sync_types' => $syncTypes,
            'agents_started' => $agentsStarted,
            'total_items' => $totalItems,
        ]);

        return [
            'success' => true,
            'message' => "Sync coordinator started {$agentsStarted} agent(s)",
            'batch_id' => $batchId,
            'sync_types' => $syncTypes,
            'agents_started' => $agentsStarted,
            'total_items' => $totalItems,
        ];
    }

    /**
     * Get the coordinator batch
     */
    public function getCoordinatorBatch(): SupplierSyncBatch
    {
        return $this->coordinatorBatch;
    }

    /**
     * Get all active agents
     */
    public function getActiveAgents(): array
    {
        return $this->activeAgents;
    }

    /**
     * Get aggregated results from all agents
     */
    public function getAgentResults(): array
    {
        return $this->agentResults;
    }

    /**
     * Record agent completion result
     *
     * Called by agents when they complete to report results back.
     */
    public function recordAgentResult(string $syncType, array $result): void
    {
        $this->agentResults[$syncType] = $result;
        $this->activeAgents[$syncType]['status'] = 'completed';

        Log::info('Agent result recorded', [
            'sync_type' => $syncType,
            'result' => $result,
        ]);

        // Check if all agents are complete
        if ($this->allAgentsComplete()) {
            $this->finalizeCoordinatorBatch();
        }
    }

    /**
     * Check if all agents have completed
     */
    private function allAgentsComplete(): bool
    {
        $activeCount = count($this->activeAgents);
        $completedCount = count(array_filter(
            $this->activeAgents,
            fn ($agent) => $agent['status'] === 'completed'
        ));

        return $activeCount > 0 && $activeCount === $completedCount;
    }

    /**
     * Finalize coordinator batch after all agents complete
     *
     * Aggregates metrics and marks coordinator batch as complete.
     */
    private function finalizeCoordinatorBatch(): void
    {
        try {
            $totalProcessed = 0;
            $totalFailed = 0;
            $totalSkipped = 0;

            foreach ($this->agentResults as $result) {
                $totalProcessed += $result['items_processed'] ?? 0;
                $totalFailed += $result['items_failed'] ?? 0;
                $totalSkipped += $result['items_skipped'] ?? 0;
            }

            $this->coordinatorBatch->update([
                'processed_items' => $totalProcessed,
                'failed_items' => $totalFailed,
                'status' => 'completed',
            ]);

            $this->coordinatorBatch->markAsCompleted();

            Log::info('Coordinator batch finalized', [
                'batch_id' => $this->coordinatorBatch->id,
                'total_processed' => $totalProcessed,
                'total_failed' => $totalFailed,
                'total_skipped' => $totalSkipped,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to finalize coordinator batch', [
                'batch_id' => $this->coordinatorBatch->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cancel all running agents
     *
     * Requests cancellation of all active sync operations.
     */
    public function cancelAllAgents(): void
    {
        foreach ($this->activeAgents as $syncType => $agentInfo) {
            try {
                $this->syncStatusService->requestCancellation($agentInfo['batch_id']);

                Log::info('Cancellation requested for agent', [
                    'sync_type' => $syncType,
                    'batch_id' => $agentInfo['batch_id'],
                ]);
            } catch (Exception $e) {
                Log::error('Failed to request cancellation', [
                    'sync_type' => $syncType,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Mark coordinator batch as cancelled
        $this->coordinatorBatch->markAsCancelled();
    }

    /**
     * Get progress summary for all agents
     *
     * Aggregates progress from all active agents.
     */
    public function getProgressSummary(): array
    {
        $summary = [
            'coordinator_batch_id' => $this->coordinatorBatch->id,
            'total_agents' => count($this->activeAgents),
            'agents' => [],
        ];

        foreach ($this->activeAgents as $syncType => $agentInfo) {
            try {
                $progress = $this->syncStatusService->getProgress($agentInfo['batch_id']);
                $summary['agents'][$syncType] = $progress;
            } catch (Exception $e) {
                Log::warning('Failed to get agent progress', [
                    'sync_type' => $syncType,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $summary;
    }
}
