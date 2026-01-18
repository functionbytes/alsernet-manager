<?php

namespace Modules\Supplier\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Supplier\Models\SupplierSyncFailure;
use Modules\Supplier\Services\PriceSyncAgent;
use Modules\Supplier\Services\SyncStatusService;

/**
 * Sync Prices Job
 *
 * Queued job that dispatches the PriceSyncAgent to synchronize supplier product prices
 * with the ERP system. Handles batch processing with date range filtering and error recovery.
 *
 * Features:
 * - Dispatches PriceSyncAgent for price synchronization
 * - Supports filtering by supplier and date range
 * - Automatic retry on failure (3 attempts)
 * - Records sync failures in SupplierSyncFailure
 * - Comprehensive error logging
 * - 1-hour timeout for large syncs
 *
 * @property int $statusId Supplier sync status ID
 * @property int|null $supplierId Optional supplier ID filter
 * @property array|null $dateRange Optional date range filter [start, end]
 * @property string|null $batchId Unique batch identifier
 * @property int $tries Number of retry attempts
 * @property int $timeout Job timeout in seconds
 */
class SyncPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Supplier sync status ID.
     */
    protected int $statusId;

    /**
     * Optional supplier ID filter.
     */
    protected ?int $supplierId;

    /**
     * Optional date range filter [start, end].
     */
    protected ?array $dateRange;

    /**
     * Optional batch ID.
     */
    protected ?string $batchId;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param  int  $statusId  Supplier sync status ID
     * @param  int|null  $supplierId  Optional supplier ID to filter sync
     * @param  array|null  $dateRange  Optional date range [start, end]
     * @param  string|null  $batchId  Optional batch identifier
     */
    public function __construct(
        int $statusId,
        ?int $supplierId = null,
        ?array $dateRange = null,
        ?string $batchId = null
    ) {
        $this->statusId = $statusId;
        $this->supplierId = $supplierId;
        $this->dateRange = $dateRange;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     *
     * @param  PriceSyncAgent  $agent  Price synchronization agent
     * @param  SyncStatusService  $statusService  Sync status service
     *
     * @throws Exception
     */
    public function handle(
        PriceSyncAgent $agent,
        SyncStatusService $statusService
    ): void {
        Log::info('Price sync job started', [
            'status_id' => $this->statusId,
            'supplier_id' => $this->supplierId,
            'date_range' => $this->dateRange,
            'batch_id' => $this->batchId,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Apply date range filter if provided
            if ($this->dateRange && count($this->dateRange) >= 2) {
                $agent->withinDateRange($this->dateRange[0], $this->dateRange[1] ?? null);
            }

            // Execute the price sync agent
            $result = $agent->execute();

            Log::info('Price sync job completed successfully', [
                'status_id' => $this->statusId,
                'supplier_id' => $this->supplierId,
                'date_range' => $this->dateRange,
                'batch_id' => $this->batchId,
                'result' => $result,
            ]);

        } catch (Exception $e) {
            $this->recordSyncFailure($e);

            Log::error('Price sync job failed', [
                'status_id' => $this->statusId,
                'supplier_id' => $this->supplierId,
                'date_range' => $this->dateRange,
                'batch_id' => $this->batchId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  Exception  $exception  The exception that caused the job to fail
     */
    public function failed(Exception $exception): void
    {
        Log::error('Price sync job failed permanently', [
            'status_id' => $this->statusId,
            'supplier_id' => $this->supplierId,
            'date_range' => $this->dateRange,
            'batch_id' => $this->batchId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $this->recordSyncFailure($exception);
    }

    /**
     * Record sync failure in the database.
     *
     * @param  Exception  $exception  The exception from the sync operation
     */
    protected function recordSyncFailure(Exception $exception): void
    {
        try {
            SupplierSyncFailure::create([
                'batch_id' => null,
                'sync_type' => 'price',
                'supplier_id' => $this->supplierId ?? 0,
                'entity_id' => null,
                'erp_id' => null,
                'changed_data' => [],
                'context' => [
                    'status_id' => $this->statusId,
                    'batch_id' => $this->batchId,
                    'date_range' => $this->dateRange,
                    'job' => self::class,
                    'attempt' => $this->attempts(),
                ],
                'error_message' => $exception->getMessage(),
                'error_code' => $exception->getCode() ?: 'UNKNOWN',
                'retry_count' => 0,
                'max_retries' => $this->tries,
                'failure_status' => 'pending',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to record price sync failure', [
                'original_error' => $exception->getMessage(),
                'recording_error' => $e->getMessage(),
            ]);
        }
    }
}
