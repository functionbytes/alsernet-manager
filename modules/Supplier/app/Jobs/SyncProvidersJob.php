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
use Modules\Supplier\Services\ProviderSyncAgent;
use Modules\Supplier\Services\SyncStatusService;

/**
 * Sync Providers Job
 *
 * Queued job that dispatches the ProviderSyncAgent to synchronize supplier providers
 * (ERP providers) with the ERP system. Handles batch processing with error recovery and failure tracking.
 *
 * Features:
 * - Dispatches ProviderSyncAgent for provider synchronization
 * - Supports filtering by provider
 * - Automatic retry on failure (3 attempts)
 * - Records sync failures in SupplierSyncFailure
 * - Comprehensive error logging
 * - 1-hour timeout for large syncs
 *
 * @property int $statusId Supplier sync status ID
 * @property int|null $providerId Optional provider ID filter
 * @property string|null $batchId Unique batch identifier
 * @property int $tries Number of retry attempts
 * @property int $timeout Job timeout in seconds
 */
class SyncProvidersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Supplier sync status ID.
     */
    protected int $statusId;

    /**
     * Optional provider ID filter.
     */
    protected ?int $providerId;

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
     * @param  int|null  $providerId  Optional provider ID to filter sync
     * @param  string|null  $batchId  Optional batch identifier
     */
    public function __construct(
        int $statusId,
        ?int $providerId = null,
        ?string $batchId = null
    ) {
        $this->statusId = $statusId;
        $this->providerId = $providerId;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     *
     * @param  ProviderSyncAgent  $agent  Provider synchronization agent
     * @param  SyncStatusService  $statusService  Sync status service
     *
     * @throws Exception
     */
    public function handle(
        ProviderSyncAgent $agent,
        SyncStatusService $statusService
    ): void {
        Log::info('Provider sync job started', [
            'status_id' => $this->statusId,
            'provider_id' => $this->providerId,
            'batch_id' => $this->batchId,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Execute the provider sync agent
            $result = $agent->execute();

            Log::info('Provider sync job completed successfully', [
                'status_id' => $this->statusId,
                'provider_id' => $this->providerId,
                'batch_id' => $this->batchId,
                'result' => $result,
            ]);

        } catch (Exception $e) {
            $this->recordSyncFailure($e);

            Log::error('Provider sync job failed', [
                'status_id' => $this->statusId,
                'provider_id' => $this->providerId,
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
        Log::error('Provider sync job failed permanently', [
            'status_id' => $this->statusId,
            'provider_id' => $this->providerId,
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
                'sync_type' => 'provider',
                'supplier_id' => 0,
                'entity_id' => null,
                'erp_id' => null,
                'changed_data' => [],
                'context' => [
                    'status_id' => $this->statusId,
                    'batch_id' => $this->batchId,
                    'provider_id' => $this->providerId,
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
            Log::error('Failed to record provider sync failure', [
                'original_error' => $exception->getMessage(),
                'recording_error' => $e->getMessage(),
            ]);
        }
    }
}
