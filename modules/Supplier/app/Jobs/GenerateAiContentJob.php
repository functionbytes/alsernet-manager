<?php

namespace Modules\Supplier\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Supplier\Entities\Supplier;
use Modules\Supplier\Entities\SupplierAiContent;
use Modules\Supplier\Entities\SupplierAiCost;
use Modules\Supplier\Entities\SupplierAutomationRateLimit;
use Modules\Supplier\Entities\SupplierExtractionResult;
use Modules\Supplier\Entities\SupplierPrompt;
use Modules\Supplier\Services\ContentGenerationService;

/**
 * Generate AI Content Job
 *
 * Queued job for generating AI-powered product content from extraction results.
 * Handles single or batch processing with rate limiting, cost tracking, and error recovery.
 *
 * Features:
 * - Single or batch processing mode
 * - AI API rate limiting (configurable per minute/hour/day)
 * - Budget limit enforcement
 * - Cost tracking per request
 * - Generation metrics logging (tokens, latency, quality)
 * - Automatic retry on failure (3 attempts)
 * - Status transition management
 * - Error handling with detailed logging
 *
 * @property array|int $resultIds Single result ID or array of result IDs
 * @property string|null $batchId Unique batch identifier for tracking
 * @property int|null $promptId Optional specific prompt to use
 * @property array $options Generation options (model, temperature, etc.)
 * @property int $tries Number of retry attempts
 * @property int $timeout Job timeout in seconds
 */
class GenerateAiContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Extraction result ID(s) to process.
     */
    protected array|int $resultIds;

    /**
     * Unique batch identifier.
     */
    protected string $batchId;

    /**
     * Optional specific prompt ID to use.
     */
    protected ?int $promptId;

    /**
     * Generation options.
     */
    protected array $options;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * The maximum number of exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * Indicates if the job should be marked as failed on timeout.
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     * @param  SupplierExtractionResult|array|int  $results  Single result, array of results, or IDs
     * @param  int|null  $promptId  Optional specific prompt ID
     * @param  array  $options  Generation options (model, temperature, max_tokens, etc.)
     */
    public function __construct(
        SupplierExtractionResult|array|int $results,
        ?int $promptId = null,
        array $options = []
    ) {
        if ($results instanceof SupplierExtractionResult) {
            $this->resultIds = [$results->id];
        } elseif (is_array($results)) {
            $this->resultIds = is_int($results[0] ?? null)
                ? $results
                : collect($results)->pluck('id')->toArray();
        } else {
            $this->resultIds = [$results];
        }

        $this->promptId = $promptId;
        $this->options = $options;
        $this->batchId = $options['batch_id'] ?? 'ai_gen_'.Str::ulid();

        $this->onQueue(config('supplier.queues.ai_generation', 'ai-generation'));

        if (count($this->resultIds) > 1) {
            $this->timeout = 600;
        }
    }

    /**
     * Execute the job.
     *
     * @param  ContentGenerationService  $contentService  Content generation service
     *
     * @throws Exception
     */
    public function handle(ContentGenerationService $contentService): void
    {
        $isBatch = count($this->resultIds) > 1;

        Log::info('AI content generation job started', [
            'batch_id' => $this->batchId,
            'result_count' => count($this->resultIds),
            'prompt_id' => $this->promptId,
            'is_batch' => $isBatch,
            'options' => $this->options,
        ]);

        $metrics = [
            'total' => count($this->resultIds),
            'successful' => 0,
            'failed' => 0,
            'rate_limited' => 0,
            'budget_exceeded' => 0,
            'total_cost' => 0.0,
            'total_tokens' => 0,
            'total_latency_ms' => 0,
            'errors' => [],
            'started_at' => now(),
        ];

        $prompt = $this->promptId ? SupplierPrompt::find($this->promptId) : null;

        foreach ($this->resultIds as $resultId) {
            try {
                $result = SupplierExtractionResult::findOrFail($resultId);

                if (! $this->checkRateLimits($result->supplier_id)) {
                    $metrics['rate_limited']++;
                    $metrics['errors'][] = [
                        'result_id' => $resultId,
                        'error' => 'Rate limit exceeded',
                        'type' => 'rate_limit',
                    ];

                    Log::warning('Rate limit exceeded, delaying generation', [
                        'batch_id' => $this->batchId,
                        'result_id' => $resultId,
                        'supplier_id' => $result->supplier_id,
                    ]);

                    if ($isBatch) {
                        sleep(2);

                        continue;
                    }

                    $this->release(60);

                    return;
                }

                if (! $this->checkBudgetLimits()) {
                    $metrics['budget_exceeded']++;
                    $metrics['errors'][] = [
                        'result_id' => $resultId,
                        'error' => 'Budget limit exceeded',
                        'type' => 'budget_exceeded',
                    ];

                    Log::error('AI budget limit exceeded', [
                        'batch_id' => $this->batchId,
                        'result_id' => $resultId,
                    ]);

                    break;
                }

                $startTime = microtime(true);

                $content = $contentService->generateContent($result, $prompt);

                $generationTime = (microtime(true) - $startTime) * 1000;

                $this->trackGenerationMetrics($content, $generationTime);

                $metadata = $content->generation_metadata ?? [];
                $metrics['successful']++;
                $metrics['total_cost'] += $metadata['cost'] ?? 0;
                $metrics['total_tokens'] += ($metadata['tokens']['total'] ?? 0);
                $metrics['total_latency_ms'] += $metadata['latency_ms'] ?? 0;

                Log::info('AI content generated successfully', [
                    'batch_id' => $this->batchId,
                    'result_id' => $resultId,
                    'content_id' => $content->id,
                    'cost' => $metadata['cost'] ?? 0,
                    'tokens' => $metadata['tokens']['total'] ?? 0,
                    'latency_ms' => $metadata['latency_ms'] ?? 0,
                ]);

                if ($isBatch && $metrics['successful'] % 10 === 0) {
                    sleep(1);
                }

            } catch (Exception $e) {
                $metrics['failed']++;
                $metrics['errors'][] = [
                    'result_id' => $resultId,
                    'error' => $e->getMessage(),
                    'type' => 'generation_error',
                ];

                Log::error('AI content generation failed', [
                    'batch_id' => $this->batchId,
                    'result_id' => $resultId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                if (! $isBatch) {
                    throw $e;
                }
            }
        }

        $metrics['completed_at'] = now();
        $metrics['duration_seconds'] = $metrics['completed_at']->diffInSeconds($metrics['started_at']);
        $metrics['avg_latency_ms'] = $metrics['successful'] > 0
            ? round($metrics['total_latency_ms'] / $metrics['successful'], 2)
            : 0;

        Log::info('AI content generation job completed', [
            'batch_id' => $this->batchId,
            'metrics' => $metrics,
        ]);

        $this->recordBatchMetrics($metrics);
    }

    /**
     * Handle a job failure.
     *
     * @param  Exception  $exception  The exception that caused the job to fail
     */
    public function failed(Exception $exception): void
    {
        Log::error('AI content generation job failed permanently', [
            'batch_id' => $this->batchId,
            'result_ids' => $this->resultIds,
            'prompt_id' => $this->promptId,
            'attempt' => $this->attempts(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        foreach ($this->resultIds as $resultId) {
            try {
                $result = SupplierExtractionResult::find($resultId);

                if ($result) {
                    $content = SupplierAiContent::where('erp_reference', $result->reference)
                        ->where('supplier_id', $result->supplier_id)
                        ->where('status', SupplierAiContent::STATUS_GENERATING)
                        ->first();

                    if ($content) {
                        $content->markAsFailed(
                            "Job failed after {$this->attempts()} attempts: {$exception->getMessage()}"
                        );
                    }
                }
            } catch (Exception $e) {
                Log::error('Error updating content status on job failure', [
                    'result_id' => $resultId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check rate limits for AI API calls.
     *
     * Configurable rate limits:
     * - Per minute (default: 10 requests)
     * - Per hour (default: 100 requests)
     * - Per day (default: 1000 requests)
     *
     * @param  int  $supplierId  Supplier ID to check limits for
     * @return bool True if within limits, false if rate limited
     */
    protected function checkRateLimits(int $supplierId): bool
    {
        $supplier = Supplier::find($supplierId);

        if (! $supplier) {
            return false;
        }

        $rateLimitConfig = config('supplier.ai.rate_limits', [
            'minute' => 10,
            'hour' => 100,
            'day' => 1000,
        ]);

        foreach ($rateLimitConfig as $window => $maxRequests) {
            $allowed = SupplierAutomationRateLimit::checkAndIncrement(
                $supplier,
                $window,
                $maxRequests
            );

            if (! $allowed) {
                Log::warning('Rate limit exceeded', [
                    'batch_id' => $this->batchId,
                    'supplier_id' => $supplierId,
                    'window' => $window,
                    'max_requests' => $maxRequests,
                ]);

                return false;
            }
        }

        return true;
    }

    /**
     * Check if AI budget limits are exceeded.
     *
     * @return bool True if within budget, false if exceeded
     */
    protected function checkBudgetLimits(): bool
    {
        try {
            $dailyLimit = config('supplier.ai.daily_budget_limit', 100.00);
            $monthlyLimit = config('supplier.ai.monthly_budget_limit', 2000.00);

            if (SupplierAiCost::isDailyBudgetExceeded($dailyLimit)) {
                Log::warning('Daily AI budget exceeded', [
                    'batch_id' => $this->batchId,
                    'limit' => $dailyLimit,
                ]);

                return false;
            }

            if (SupplierAiCost::isMonthlyBudgetExceeded($monthlyLimit)) {
                Log::warning('Monthly AI budget exceeded', [
                    'batch_id' => $this->batchId,
                    'limit' => $monthlyLimit,
                ]);

                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::error('Error checking budget limits', [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
            ]);

            return true;
        }
    }

    /**
     * Track generation metrics for monitoring and analytics.
     *
     * @param  SupplierAiContent  $content  Generated content
     * @param  float  $generationTime  Total generation time in milliseconds
     */
    protected function trackGenerationMetrics(SupplierAiContent $content, float $generationTime): void
    {
        $metadata = $content->generation_metadata ?? [];

        Log::debug('Generation metrics tracked', [
            'batch_id' => $this->batchId,
            'content_id' => $content->id,
            'supplier_id' => $content->supplier_id,
            'model' => $metadata['model'] ?? 'unknown',
            'input_tokens' => $metadata['tokens']['input'] ?? 0,
            'output_tokens' => $metadata['tokens']['output'] ?? 0,
            'total_tokens' => $metadata['tokens']['total'] ?? 0,
            'cost' => $metadata['cost'] ?? 0,
            'latency_ms' => $metadata['latency_ms'] ?? 0,
            'total_generation_time_ms' => round($generationTime, 2),
        ]);
    }

    /**
     * Record batch processing metrics.
     *
     * @param  array  $metrics  Batch metrics
     */
    protected function recordBatchMetrics(array $metrics): void
    {
        try {
            DB::table('supplier_ai_batch_metrics')->insert([
                'batch_id' => $this->batchId,
                'total_items' => $metrics['total'],
                'successful' => $metrics['successful'],
                'failed' => $metrics['failed'],
                'rate_limited' => $metrics['rate_limited'],
                'budget_exceeded' => $metrics['budget_exceeded'],
                'total_cost' => $metrics['total_cost'],
                'total_tokens' => $metrics['total_tokens'],
                'avg_latency_ms' => $metrics['avg_latency_ms'],
                'duration_seconds' => $metrics['duration_seconds'],
                'errors' => json_encode($metrics['errors']),
                'started_at' => $metrics['started_at'],
                'completed_at' => $metrics['completed_at'],
                'created_at' => now(),
            ]);

            Log::info('Batch metrics recorded', [
                'batch_id' => $this->batchId,
                'success_rate' => $metrics['total'] > 0
                    ? round(($metrics['successful'] / $metrics['total']) * 100, 2)
                    : 0,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to record batch metrics', [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->batchId;
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'supplier:ai-generation',
            'batch:'.$this->batchId,
            'count:'.count($this->resultIds),
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): int
    {
        return 60 * $this->attempts();
    }

    /**
     * Determine if the job should be retried based on the exception.
     *
     * @param  Exception  $exception  The exception that occurred
     */
    public function shouldRetry(Exception $exception): bool
    {
        $retryableErrors = [
            'timeout',
            'connection',
            'rate limit',
            'service unavailable',
            '429',
            '503',
            '504',
        ];

        $message = strtolower($exception->getMessage());

        foreach ($retryableErrors as $error) {
            if (str_contains($message, $error)) {
                return true;
            }
        }

        return false;
    }
}
