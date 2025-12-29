<?php

namespace App\Jobs\Supplier;

use App\Models\Supplier\SupplierExtractionBatch;
use App\Models\Supplier\SupplierSource;
use App\Services\Supplier\ExtractionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process Supplier Extraction Job
 *
 * Processes supplier data extraction from configured sources using the ExtractionService.
 * Implements automatic retry logic with exponential backoff for transient failures.
 * Updates extraction batch status throughout the process and dispatches AI content
 * generation jobs for new or updated inventaries.
 *
 * @property int $tries Maximum number of retry attempts
 * @property int $timeout Job timeout in seconds
 * @property int $backoff Backoff delay in seconds between retries
 * @property bool $failOnTimeout Whether to fail the job on timeout
 */
class ProcessSupplierExtractionJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * The number of seconds to wait before retrying the job.
     * Uses exponential backoff: attempt 1 = 30s, attempt 2 = 60s, attempt 3 = 120s
     *
     * @var array<int>
     */
    public array $backoff = [30, 60, 120];

    /**
     * Indicate if the job should be marked as failed on timeout.
     */
    public bool $failOnTimeout = true;

    /**
     * The supplier source to extract data from.
     */
    protected SupplierSource $source;

    /**
     * The batch ID for grouping extraction results.
     */
    protected ?string $batchId;

    /**
     * Create a new job instance.
     *
     * @param  SupplierSource  $source  The supplier source to process
     * @param  string|null  $batchId  Optional batch ID for grouping results
     */
    public function __construct(SupplierSource $source, ?string $batchId = null)
    {
        $this->source = $source;
        $this->batchId = $batchId;
        $this->onQueue('supplier-extraction');
    }

    /**
     * Execute the job.
     *
     * Processes the supplier extraction using ExtractionService, tracks progress,
     * handles errors, and dispatches follow-up jobs for AI content generation.
     */
    public function handle(ExtractionService $extractionService): void
    {
        $batch = null;

        try {
            Log::info('Starting supplier extraction job', [
                'source_id' => $this->source->id,
                'source_label' => $this->source->label,
                'batch_id' => $this->batchId,
                'attempt' => $this->attempts(),
            ]);

            // Find or create extraction batch
            if ($this->batchId) {
                $batch = SupplierExtractionBatch::where('uid', $this->batchId)->first();
            }

            if (! $batch) {
                $batch = $extractionService->startExtraction($this->source, 'manual');
                $this->batchId = $batch->uid;
            }

            // Mark batch as processing if not already
            if ($batch->isPending()) {
                $batch->markAsStarted();
            }

            // Process the extraction based on source type
            $this->processExtractionBySourceType($extractionService, $batch);

            // Update batch metrics
            $extractionService->updateBatchMetrics($batch);

            // Complete the extraction batch
            $extractionService->completeExtraction($batch);

            // Dispatch AI content generation jobs for new/updated inventaries
            $this->dispatchAiContentJobs($batch);

            Log::info('Supplier extraction job completed successfully', [
                'source_id' => $this->source->id,
                'batch_id' => $batch->uid,
                'total_items' => $batch->total_items,
                'new_items' => $batch->new_items,
                'updated_items' => $batch->updated_items,
            ]);

        } catch (\Exception $e) {
            Log::error('Supplier extraction job failed', [
                'source_id' => $this->source->id,
                'batch_id' => $this->batchId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark batch as failed if this is the last attempt
            if ($batch && $this->attempts() >= $this->tries) {
                $batch->markAsFailed();

                Log::critical('Supplier extraction job permanently failed after all retries', [
                    'source_id' => $this->source->id,
                    'batch_id' => $batch->uid,
                    'total_attempts' => $this->attempts(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Process extraction based on source type.
     *
     * @param  ExtractionService  $extractionService  The extraction service
     * @param  SupplierExtractionBatch  $batch  The extraction batch
     */
    protected function processExtractionBySourceType(
        ExtractionService $extractionService,
        SupplierExtractionBatch $batch
    ): void {
        if ($this->source->isWebsite()) {
            $this->processWebsiteExtraction($extractionService, $batch);
        } elseif ($this->source->isApi()) {
            $this->processApiExtraction($extractionService, $batch);
        } elseif ($this->source->isFtp()) {
            $this->processFtpExtraction($extractionService, $batch);
        } elseif ($this->source->isFile()) {
            $this->processFileExtraction($extractionService, $batch);
        } else {
            throw new \InvalidArgumentException("Unsupported source type: {$this->source->source_type}");
        }
    }

    /**
     * Process website extraction.
     *
     * @param  ExtractionService  $extractionService  The extraction service
     * @param  SupplierExtractionBatch  $batch  The extraction batch
     */
    protected function processWebsiteExtraction(
        ExtractionService $extractionService,
        SupplierExtractionBatch $batch
    ): void {
        // Trigger orchestrator workflow for website scraping
        $execution = $extractionService->triggerOrchestratorWorkflow($this->source, 'scraping');

        if ($execution) {
            Log::info('Website extraction orchestrator triggered', [
                'source_id' => $this->source->id,
                'execution_id' => $execution->uid,
                'batch_id' => $batch->uid,
            ]);
        }
    }

    /**
     * Process API extraction.
     *
     * @param  ExtractionService  $extractionService  The extraction service
     * @param  SupplierExtractionBatch  $batch  The extraction batch
     */
    protected function processApiExtraction(
        ExtractionService $extractionService,
        SupplierExtractionBatch $batch
    ): void {
        // Trigger orchestrator workflow for API extraction
        $execution = $extractionService->triggerOrchestratorWorkflow($this->source, 'api_extraction');

        if ($execution) {
            Log::info('API extraction orchestrator triggered', [
                'source_id' => $this->source->id,
                'execution_id' => $execution->uid,
                'batch_id' => $batch->uid,
            ]);
        }
    }

    /**
     * Process FTP extraction.
     *
     * @param  ExtractionService  $extractionService  The extraction service
     * @param  SupplierExtractionBatch  $batch  The extraction batch
     */
    protected function processFtpExtraction(
        ExtractionService $extractionService,
        SupplierExtractionBatch $batch
    ): void {
        // Trigger orchestrator workflow for FTP sync
        $execution = $extractionService->triggerOrchestratorWorkflow($this->source, 'ftp_sync');

        if ($execution) {
            Log::info('FTP extraction orchestrator triggered', [
                'source_id' => $this->source->id,
                'execution_id' => $execution->uid,
                'batch_id' => $batch->uid,
            ]);
        }
    }

    /**
     * Process file extraction.
     *
     * @param  ExtractionService  $extractionService  The extraction service
     * @param  SupplierExtractionBatch  $batch  The extraction batch
     */
    protected function processFileExtraction(
        ExtractionService $extractionService,
        SupplierExtractionBatch $batch
    ): void {
        // Trigger orchestrator workflow for file extraction
        $execution = $extractionService->triggerOrchestratorWorkflow($this->source, 'file_extraction');

        if ($execution) {
            Log::info('File extraction orchestrator triggered', [
                'source_id' => $this->source->id,
                'execution_id' => $execution->uid,
                'batch_id' => $batch->uid,
            ]);
        }
    }

    /**
     * Dispatch AI content generation jobs for new and updated inventaries.
     *
     * @param  SupplierExtractionBatch  $batch  The extraction batch
     */
    protected function dispatchAiContentJobs(SupplierExtractionBatch $batch): void
    {
        // Get new and updated extraction results
        $results = $batch->results()
            ->whereIn('status', ['new', 'updated'])
            ->get();

        if ($results->isEmpty()) {
            Log::info('No new or updated inventaries found, skipping AI content generation', [
                'batch_id' => $batch->uid,
            ]);

            return;
        }

        Log::info('Dispatching AI content generation jobs', [
            'batch_id' => $batch->uid,
            'total_results' => $results->count(),
        ]);

        foreach ($results as $result) {
            // Check if GenerateAiContentJob exists before dispatching
            if (class_exists(\App\Jobs\Supplier\GenerateAiContentJob::class)) {
                \App\Jobs\Supplier\GenerateAiContentJob::dispatch($result)
                    ->onQueue('ai-content-generation');

                Log::debug('AI content generation job dispatched', [
                    'result_id' => $result->id,
                    'reference' => $result->reference,
                    'batch_id' => $batch->uid,
                ]);
            } else {
                Log::warning('GenerateAiContentJob class not found, skipping AI content generation', [
                    'result_id' => $result->id,
                ]);
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception  The exception that caused the failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Supplier extraction job permanently failed', [
            'source_id' => $this->source->id,
            'batch_id' => $this->batchId,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Mark batch as failed if it exists
        if ($this->batchId) {
            $batch = SupplierExtractionBatch::where('uid', $this->batchId)->first();

            if ($batch) {
                $batch->markAsFailed();
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'supplier-extraction',
            "source:{$this->source->id}",
            "supplier:{$this->source->supplier_id}",
            $this->batchId ? "batch:{$this->batchId}" : null,
        ];
    }
}
