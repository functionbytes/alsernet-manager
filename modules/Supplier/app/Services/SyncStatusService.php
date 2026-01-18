<?php

namespace Modules\Supplier\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Supplier\Models\SupplierSyncBatch;
use Modules\Supplier\Models\SupplierSyncFailure;
use Modules\Supplier\Models\SupplierSyncLog;
use Modules\Supplier\Models\SupplierSyncStatus;

/**
 * Core state management service for Supplier synchronization operations
 *
 * Manages the lifecycle of sync operations with dual persistence:
 * - Redis for real-time progress tracking (fast access, cache)
 * - Database for persistent state and audit trail
 *
 * Supports concurrent sync operations using cache keys, retry logic,
 * failure tracking, and event broadcasting for real-time updates.
 */
class SyncStatusService
{
    private const CACHE_TTL = 86400; // 24 hours

    private const PROGRESS_CACHE_TTL = 3600; // 1 hour for progress updates

    private const CANCEL_FLAG_TTL = 3600; // 1 hour for cancellation flags

    private const CACHE_PREFIX = 'sync:';

    public function __construct() {}

    /**
     * Start a new synchronization operation
     *
     * Creates or retrieves a sync status and marks it as running.
     * Initializes Redis cache for progress tracking.
     */
    public function startSync(
        SupplierSyncBatch $batch,
        int $totalItems,
        string $triggeredBy = 'manual',
        ?array $metadata = null
    ): SupplierSyncStatus {
        DB::beginTransaction();

        try {
            $status = SupplierSyncStatus::create([
                'supplier_id' => $batch->supplier_id,
                'batch_id' => $batch->id,
                'sync_type' => $batch->sync_type,
                'sync_scope' => $batch->supplier_id ? 'per_supplier' : 'all',
                'status' => 'pending',
                'total_items' => $totalItems,
                'synced_items' => 0,
                'failed_items' => 0,
                'skipped_items' => 0,
                'triggered_by' => $triggeredBy,
                'metadata' => $metadata,
            ]);

            $batch->markAsStarted();
            $status->markAsStarted();

            // Initialize Redis cache with status data
            $this->initializeRedisStatus($status);

            DB::commit();

            Log::info('Sync started', [
                'status_id' => $status->id,
                'batch_id' => $batch->id,
                'sync_type' => $batch->sync_type,
                'total_items' => $totalItems,
                'triggered_by' => $triggeredBy,
            ]);

            return $status->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to start sync', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update progress of an ongoing sync operation
     *
     * Updates both Redis cache and database with progress metrics.
     * Broadcasts progress event for real-time UI updates.
     */
    public function updateProgress(
        SupplierSyncStatus $status,
        int $syncedCount,
        int $failedCount = 0,
        int $skippedCount = 0
    ): SupplierSyncStatus {
        // Update database
        $status->update([
            'synced_items' => $syncedCount,
            'failed_items' => $failedCount,
            'skipped_items' => $skippedCount,
        ]);

        // Update Redis cache for real-time access
        $this->updateRedisProgress($status);

        // Emit broadcast event for real-time UI
        broadcast(new \Modules\Supplier\Events\SyncProgressUpdated($status))->toOthers();

        return $status->fresh();
    }

    /**
     * Mark a sync operation as completed successfully
     *
     * Updates status, calculates metrics, cleans up Redis cache,
     * and broadcasts completion event.
     */
    public function completeSync(
        SupplierSyncStatus $status,
        ?array $finalMetadata = null
    ): SupplierSyncStatus {
        DB::beginTransaction();

        try {
            $completedAt = now();
            $startedAt = $status->started_at;
            $elapsedSeconds = $startedAt ? $completedAt->diffInSeconds($startedAt) : 0;
            $memoryUsedMb = memory_get_peak_usage(true) / (1024 * 1024);

            $updateData = [
                'status' => 'completed',
                'completed_at' => $completedAt,
                'elapsed_seconds' => $elapsedSeconds,
                'memory_used_mb' => $memoryUsedMb,
            ];

            if ($finalMetadata) {
                $updateData['metadata'] = array_merge($status->metadata ?? [], $finalMetadata);
            }

            $status->update($updateData);

            // Update batch
            $batch = $status->batch;
            if ($batch) {
                $batch->update([
                    'processed_items' => $status->synced_items,
                    'failed_items' => $status->failed_items,
                ]);
                $batch->markAsCompleted();
            }

            // Clean up Redis cache
            $this->cleanupRedisStatus($status);

            // Broadcast completion event
            broadcast(new \Modules\Supplier\Events\SyncCompleted($status))->toOthers();

            DB::commit();

            Log::info('Sync completed', [
                'status_id' => $status->id,
                'batch_id' => $status->batch_id,
                'synced_items' => $status->synced_items,
                'failed_items' => $status->failed_items,
                'elapsed_seconds' => $elapsedSeconds,
                'memory_used_mb' => $memoryUsedMb,
            ]);

            return $status->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete sync', [
                'status_id' => $status->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Mark a sync operation as failed
     *
     * Records failure reason, updates status, handles batch failure,
     * and cleans up resources.
     */
    public function failSync(
        SupplierSyncStatus $status,
        string $failureReason,
        ?string $failureCode = null
    ): SupplierSyncStatus {
        DB::beginTransaction();

        try {
            $completedAt = now();
            $startedAt = $status->started_at;
            $elapsedSeconds = $startedAt ? $completedAt->diffInSeconds($startedAt) : 0;

            $status->update([
                'status' => 'failed',
                'completed_at' => $completedAt,
                'elapsed_seconds' => $elapsedSeconds,
                'notes' => $failureReason,
                'metadata' => array_merge($status->metadata ?? [], [
                    'failure_code' => $failureCode,
                    'failure_reason' => $failureReason,
                ]),
            ]);

            // Update batch
            $batch = $status->batch;
            if ($batch) {
                $batch->markAsFailed();
            }

            // Clean up Redis
            $this->cleanupRedisStatus($status);

            // Broadcast failure event
            broadcast(new \Modules\Supplier\Events\SyncFailed($status, $failureReason))->toOthers();

            DB::commit();

            Log::warning('Sync failed', [
                'status_id' => $status->id,
                'batch_id' => $status->batch_id,
                'reason' => $failureReason,
                'code' => $failureCode,
                'elapsed_seconds' => $elapsedSeconds,
            ]);

            return $status->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark sync as failed', [
                'status_id' => $status->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get current status of a sync operation
     *
     * Retrieves from database with related relationships for full context.
     */
    public function getStatus(int $statusId): ?SupplierSyncStatus
    {
        return SupplierSyncStatus::with(['batch', 'supplier', 'logs', 'failures'])
            ->find($statusId);
    }

    /**
     * Get progress data for a sync operation
     *
     * Returns current progress metrics, both from cache and database.
     */
    public function getProgress(int $statusId): array
    {
        // Try to get from Redis cache first (faster)
        $cacheKey = $this->getProgressCacheKey($statusId);
        $cachedProgress = Cache::get($cacheKey);

        if ($cachedProgress) {
            return $cachedProgress;
        }

        // Fall back to database
        $status = $this->getStatus($statusId);

        if (! $status) {
            return [
                'status' => 'not_found',
                'error' => 'Sync status not found',
            ];
        }

        $progress = [
            'status_id' => $status->id,
            'batch_id' => $status->batch_id,
            'sync_type' => $status->sync_type,
            'current_status' => $status->status,
            'total_items' => $status->total_items,
            'synced_items' => $status->synced_items,
            'failed_items' => $status->failed_items,
            'skipped_items' => $status->skipped_items,
            'progress_percentage' => $status->progress_percentage,
            'success_rate' => $status->success_rate,
            'started_at' => $status->started_at?->toIso8601String(),
            'completed_at' => $status->completed_at?->toIso8601String(),
            'elapsed_seconds' => $status->elapsed_seconds,
            'memory_used_mb' => $status->memory_used_mb,
        ];

        // Cache for quick retrieval
        Cache::put($cacheKey, $progress, self::PROGRESS_CACHE_TTL);

        return $progress;
    }

    /**
     * Check if a sync operation has been cancelled
     *
     * Checks Redis cache for cancellation flag.
     */
    public function isCancellationRequested(int $batchId): bool
    {
        $cancelKey = self::CACHE_PREFIX."cancel:{$batchId}";

        return (bool) Cache::get($cancelKey);
    }

    /**
     * Request cancellation of a sync operation
     *
     * Sets a flag in Redis that sync agents can check to gracefully stop.
     */
    public function requestCancellation(int $batchId): void
    {
        $cancelKey = self::CACHE_PREFIX."cancel:{$batchId}";
        Cache::put($cancelKey, true, self::CANCEL_FLAG_TTL);

        Log::info('Sync cancellation requested', ['batch_id' => $batchId]);
    }

    /**
     * Log a sync action/event
     *
     * Records detailed log entry in database for audit trail.
     */
    public function logAction(
        SupplierSyncBatch $batch,
        string $entityType,
        ?int $entityId,
        string $action,
        string $result,
        ?string $message = null,
        ?array $dataBefore = null,
        ?array $dataAfter = null,
        ?array $changes = null,
        ?string $errorCode = null,
        ?string $errorMessage = null,
        int $durationMs = 0
    ): SupplierSyncLog {
        return SupplierSyncLog::create([
            'batch_id' => $batch->id,
            'status_id' => $batch->statuses()->latest()->first()?->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'result' => $result,
            'message' => $message,
            'data_before' => $dataBefore,
            'data_after' => $dataAfter,
            'changes' => $changes,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
            'triggered_by' => 'sync_job',
            'synced_at' => now(),
        ]);
    }

    /**
     * Record a sync failure for retry processing
     *
     * Creates a failure record that can be processed for manual/automatic retry.
     */
    public function recordFailure(
        SupplierSyncBatch $batch,
        string $syncType,
        int $supplierId,
        ?int $entityId,
        ?int $erpId,
        array $changedData,
        string $errorMessage,
        ?string $errorCode = null,
        ?array $context = null,
        int $maxRetries = 3
    ): SupplierSyncFailure {
        return SupplierSyncFailure::create([
            'batch_id' => $batch->id,
            'sync_type' => $syncType,
            'supplier_id' => $supplierId,
            'entity_id' => $entityId,
            'erp_id' => $erpId,
            'changed_data' => $changedData,
            'context' => $context,
            'error_message' => $errorMessage,
            'error_code' => $errorCode,
            'retry_count' => 0,
            'max_retries' => $maxRetries,
            'failure_status' => 'pending',
        ]);
    }

    /**
     * Check if sync can be retried and handle retry logic
     *
     * Determines if a batch can be retried based on attempt count.
     */
    public function canRetryBatch(SupplierSyncBatch $batch): bool
    {
        return $batch->canRetry();
    }

    /**
     * Retry a failed sync batch
     *
     * Increments retry counter and resets status for retry.
     */
    public function retryBatch(SupplierSyncBatch $batch): SupplierSyncBatch
    {
        DB::beginTransaction();

        try {
            $batch->incrementRetryAttempt();

            $batch->update([
                'status' => 'pending',
                'processed_items' => 0,
                'failed_items' => 0,
                'processed_batches' => 0,
            ]);

            DB::commit();

            Log::info('Batch retry initiated', [
                'batch_id' => $batch->id,
                'retry_attempt' => $batch->retry_attempt,
            ]);

            return $batch->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to retry batch', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Initialize Redis cache for a sync status
     */
    private function initializeRedisStatus(SupplierSyncStatus $status): void
    {
        $cacheKey = $this->getStatusCacheKey($status->id);

        $statusData = [
            'id' => $status->id,
            'batch_id' => $status->batch_id,
            'sync_type' => $status->sync_type,
            'status' => $status->status,
            'total_items' => $status->total_items,
            'started_at' => $status->started_at?->toIso8601String(),
        ];

        Cache::put($cacheKey, $statusData, self::CACHE_TTL);
    }

    /**
     * Update progress in Redis cache
     */
    private function updateRedisProgress(SupplierSyncStatus $status): void
    {
        $cacheKey = $this->getProgressCacheKey($status->id);

        $progress = [
            'status_id' => $status->id,
            'batch_id' => $status->batch_id,
            'total_items' => $status->total_items,
            'synced_items' => $status->synced_items,
            'failed_items' => $status->failed_items,
            'skipped_items' => $status->skipped_items,
            'progress_percentage' => $status->progress_percentage,
            'success_rate' => $status->success_rate,
            'updated_at' => now()->toIso8601String(),
        ];

        Cache::put($cacheKey, $progress, self::PROGRESS_CACHE_TTL);
    }

    /**
     * Clean up Redis cache for a sync status
     */
    private function cleanupRedisStatus(SupplierSyncStatus $status): void
    {
        Cache::forget($this->getStatusCacheKey($status->id));
        Cache::forget($this->getProgressCacheKey($status->id));
    }

    /**
     * Get Redis cache key for status data
     */
    private function getStatusCacheKey(int $statusId): string
    {
        return self::CACHE_PREFIX."status:{$statusId}";
    }

    /**
     * Get Redis cache key for progress data
     */
    private function getProgressCacheKey(int $statusId): string
    {
        return self::CACHE_PREFIX."progress:{$statusId}";
    }
}
