<?php

namespace Modules\Supplier\Services;

use Modules\Supplier\Entities\Supplier;
use Modules\Supplier\Entities\SupplierAiContent;
use Modules\Supplier\Entities\SupplierAiCost;
use Modules\Supplier\Entities\SupplierContentValidation;
use Modules\Supplier\Entities\SupplierExtractionResult;
use Modules\Supplier\Entities\SupplierPrompt;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Content Generation Service for Supplier Automation System
 *
 * Handles AI-powered content generation using OpenAI/Anthropic APIs with:
 * - 6-level prompt resolution cascade
 * - Content generation and regeneration
 * - Batch processing
 * - Quality validation
 * - Cost tracking and budget management
 * - Workflow management (review, approval, rejection)
 */
class ContentGenerationService
{
    /**
     * AI Model configurations with pricing per 1M tokens
     */
    protected const MODEL_CONFIG = [
        'gpt-4o' => [
            'provider' => 'openai',
            'input_cost_per_1m' => 2.50,
            'output_cost_per_1m' => 10.00,
            'max_tokens' => 4096,
        ],
        'gpt-4o-mini' => [
            'provider' => 'openai',
            'input_cost_per_1m' => 0.150,
            'output_cost_per_1m' => 0.600,
            'max_tokens' => 16384,
        ],
        'claude-3-5-sonnet' => [
            'provider' => 'anthropic',
            'input_cost_per_1m' => 3.00,
            'output_cost_per_1m' => 15.00,
            'max_tokens' => 8192,
        ],
        'claude-3-5-haiku' => [
            'provider' => 'anthropic',
            'input_cost_per_1m' => 0.80,
            'output_cost_per_1m' => 4.00,
            'max_tokens' => 8192,
        ],
    ];

    /**
     * Quality score thresholds
     */
    protected const QUALITY_EXCELLENT = 85;

    protected const QUALITY_GOOD = 70;

    protected const QUALITY_ACCEPTABLE = 50;

    /**
     * Readability score thresholds (Flesch Reading Ease)
     */
    protected const READABILITY_EASY = 60;

    protected const READABILITY_MODERATE = 50;

    /**
     * OpenAI API Key
     */
    protected ?string $openaiApiKey;

    /**
     * Anthropic API Key
     */
    protected ?string $anthropicApiKey;

    /**
     * Daily budget limit in USD
     */
    protected float $dailyBudgetLimit;

    /**
     * Monthly budget limit in USD
     */
    protected float $monthlyBudgetLimit;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
        $this->anthropicApiKey = config('services.anthropic.api_key');
        $this->dailyBudgetLimit = config('supplier.ai.daily_budget_limit', 100.00);
        $this->monthlyBudgetLimit = config('supplier.ai.monthly_budget_limit', 2000.00);
    }

    /**
     * Resolve the best matching prompt using 6-level priority cascade.
     *
     * Priority order (highest to lowest):
     * 1. Prompt specific to SOURCE + SUPPLIER + CATEGORY
     * 2. Prompt specific to SUPPLIER + CATEGORY
     * 3. Prompt specific to SOURCE + SUPPLIER
     * 4. Prompt specific to SUPPLIER
     * 5. Prompt specific to CATEGORY
     * 6. Global default prompt
     *
     * @param  int  $supplierId  Supplier ID
     * @param  int|null  $categoryId  Category ID (optional)
     * @param  int|null  $sourceId  Source ID (optional)
     *
     * @throws Exception When no prompt can be resolved
     */
    public function resolvePrompt(int $supplierId, ?int $categoryId = null, ?int $sourceId = null): SupplierPrompt
    {
        $prompt = SupplierPrompt::resolvePrompt($supplierId, $categoryId, $sourceId);

        if (! $prompt) {
            Log::error('Failed to resolve prompt', [
                'supplier_id' => $supplierId,
                'category_id' => $categoryId,
                'source_id' => $sourceId,
            ]);

            throw new Exception('No active prompt found for the given parameters. Please configure a global default prompt.');
        }

        Log::info('Prompt resolved', [
            'prompt_id' => $prompt->id,
            'prompt_scope' => $prompt->scope,
            'supplier_id' => $supplierId,
            'category_id' => $categoryId,
            'source_id' => $sourceId,
        ]);

        return $prompt;
    }

    /**
     * Generate AI content from extraction result.
     *
     * @param  SupplierExtractionResult  $result  Extraction result with product data
     * @param  SupplierPrompt|null  $prompt  Optional specific prompt to use
     * @return SupplierAiContent Generated content record
     *
     * @throws Exception When generation fails or budget exceeded
     */
    public function generateContent(SupplierExtractionResult $result, ?SupplierPrompt $prompt = null): SupplierAiContent
    {
        if (! $prompt) {
            $prompt = $this->resolvePrompt(
                $result->supplier_id,
                $result->extracted_data['category_id'] ?? null,
                $result->source_id
            );
        }

        $this->checkBudgetLimits();

        $content = SupplierAiContent::create([
            'supplier_id' => $result->supplier_id,
            'erp_reference' => $result->reference,
            'ean' => $result->ean,
            'status' => SupplierAiContent::STATUS_GENERATING,
            'prompt_id' => $prompt->id,
            'source_attributes' => $result->extracted_data,
        ]);

        try {
            $renderedPrompt = $this->renderPrompt($prompt, $this->prepareVariables($result));

            $aiResponse = $this->callAiApi($renderedPrompt, [
                'model' => config('supplier.ai.default_model', 'gpt-4o-mini'),
                'max_tokens' => 4000,
            ]);

            $parsedContent = $this->parseAiResponse($aiResponse['content']);

            $content->update([
                'generated_name' => $parsedContent['name'] ?? null,
                'short_description' => $parsedContent['short_description'] ?? null,
                'long_description' => $parsedContent['long_description'] ?? null,
                'bullet_points' => $parsedContent['bullet_points'] ?? null,
                'seo_title' => $parsedContent['seo_title'] ?? null,
                'seo_description' => $parsedContent['seo_description'] ?? null,
                'seo_keywords' => $parsedContent['seo_keywords'] ?? null,
                'generation_metadata' => [
                    'model' => $aiResponse['model'],
                    'tokens' => $aiResponse['tokens'],
                    'cost' => $aiResponse['cost'],
                    'latency_ms' => $aiResponse['latency_ms'],
                    'generated_at' => now()->toIso8601String(),
                ],
            ]);

            $this->trackAiCost(
                model: $aiResponse['model'],
                inputTokens: $aiResponse['tokens']['input'],
                outputTokens: $aiResponse['tokens']['output'],
                cost: $aiResponse['cost'],
                contentId: $content->id,
                supplierId: $result->supplier_id,
                batchId: $result->batch_id
            );

            $content->markAsGenerated();
            $content->log(SupplierAiContent::ACTION_GENERATION_COMPLETED);

            Log::info('Content generated successfully', [
                'content_id' => $content->id,
                'supplier_id' => $result->supplier_id,
                'model' => $aiResponse['model'],
            ]);

            return $content->fresh();
        } catch (Exception $e) {
            $content->markAsFailed($e->getMessage());
            $content->log(SupplierAiContent::ACTION_GENERATION_FAILED, null, null, [
                'error' => $e->getMessage(),
            ]);

            Log::error('Content generation failed', [
                'content_id' => $content->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Regenerate AI content with optional new prompt.
     *
     * @param  SupplierAiContent  $content  Content to regenerate
     * @param  SupplierPrompt|null  $newPrompt  Optional new prompt to use
     * @return SupplierAiContent Regenerated content record
     *
     * @throws Exception When regeneration fails
     */
    public function regenerateContent(SupplierAiContent $content, ?SupplierPrompt $newPrompt = null): SupplierAiContent
    {
        if (! $newPrompt) {
            $newPrompt = $content->prompt ?? $this->resolvePrompt(
                $content->supplier_id,
                $content->source_attributes['category_id'] ?? null,
                null
            );
        }

        $this->checkBudgetLimits();

        $content->update([
            'status' => SupplierAiContent::STATUS_GENERATING,
            'prompt_id' => $newPrompt->id,
        ]);

        try {
            $variables = $this->prepareVariablesFromContent($content);
            $renderedPrompt = $this->renderPrompt($newPrompt, $variables);

            $aiResponse = $this->callAiApi($renderedPrompt, [
                'model' => config('supplier.ai.default_model', 'gpt-4o-mini'),
                'max_tokens' => 4000,
            ]);

            $parsedContent = $this->parseAiResponse($aiResponse['content']);

            $content->update([
                'generated_name' => $parsedContent['name'] ?? null,
                'short_description' => $parsedContent['short_description'] ?? null,
                'long_description' => $parsedContent['long_description'] ?? null,
                'bullet_points' => $parsedContent['bullet_points'] ?? null,
                'seo_title' => $parsedContent['seo_title'] ?? null,
                'seo_description' => $parsedContent['seo_description'] ?? null,
                'seo_keywords' => $parsedContent['seo_keywords'] ?? null,
                'generation_metadata' => [
                    'model' => $aiResponse['model'],
                    'tokens' => $aiResponse['tokens'],
                    'cost' => $aiResponse['cost'],
                    'latency_ms' => $aiResponse['latency_ms'],
                    'regenerated_at' => now()->toIso8601String(),
                ],
            ]);

            $this->trackAiCost(
                model: $aiResponse['model'],
                inputTokens: $aiResponse['tokens']['input'],
                outputTokens: $aiResponse['tokens']['output'],
                cost: $aiResponse['cost'],
                contentId: $content->id,
                supplierId: $content->supplier_id,
                operationType: 'regeneration'
            );

            $content->markAsGenerated();
            $content->log(SupplierAiContent::ACTION_GENERATION_COMPLETED, null, null, [
                'regenerated' => true,
            ]);

            Log::info('Content regenerated successfully', [
                'content_id' => $content->id,
                'model' => $aiResponse['model'],
            ]);

            return $content->fresh();
        } catch (Exception $e) {
            $content->markAsFailed($e->getMessage());
            $content->log(SupplierAiContent::ACTION_GENERATION_FAILED, null, null, [
                'error' => $e->getMessage(),
                'regeneration_attempt' => true,
            ]);

            Log::error('Content regeneration failed', [
                'content_id' => $content->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate content for multiple extraction results in batch.
     *
     * @param  array  $resultIds  Array of extraction result IDs
     * @param  int|null  $promptId  Optional specific prompt ID to use for all
     * @return array Results array with success/failure details
     */
    public function generateBatch(array $resultIds, ?int $promptId = null): array
    {
        $results = [
            'total' => count($resultIds),
            'successful' => 0,
            'failed' => 0,
            'details' => [],
        ];

        $prompt = $promptId ? SupplierPrompt::find($promptId) : null;

        foreach ($resultIds as $resultId) {
            try {
                $extractionResult = SupplierExtractionResult::findOrFail($resultId);

                $content = $this->generateContent($extractionResult, $prompt);

                $results['successful']++;
                $results['details'][] = [
                    'result_id' => $resultId,
                    'content_id' => $content->id,
                    'status' => 'success',
                ];
            } catch (Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'result_id' => $resultId,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];

                Log::error('Batch generation failed for result', [
                    'result_id' => $resultId,
                    'error' => $e->getMessage(),
                ]);
            }

            usleep(500000);
        }

        Log::info('Batch generation completed', $results);

        return $results;
    }

    /**
     * Call AI API (OpenAI or Anthropic) to generate content.
     *
     * @param  string  $prompt  Rendered prompt text
     * @param  array  $options  API call options (model, max_tokens, temperature, etc.)
     * @return array Response with content, tokens, cost, and metadata
     *
     * @throws Exception When API call fails
     */
    public function callAiApi(string $prompt, array $options = []): array
    {
        $model = $options['model'] ?? 'gpt-4o-mini';
        $maxTokens = $options['max_tokens'] ?? 4000;
        $temperature = $options['temperature'] ?? 0.7;

        if (! isset(self::MODEL_CONFIG[$model])) {
            throw new Exception("Unsupported AI model: {$model}");
        }

        $config = self::MODEL_CONFIG[$model];
        $provider = $config['provider'];

        $startTime = microtime(true);

        try {
            if ($provider === 'openai') {
                $response = $this->callOpenAi($prompt, $model, $maxTokens, $temperature);
            } elseif ($provider === 'anthropic') {
                $response = $this->callAnthropic($prompt, $model, $maxTokens, $temperature);
            } else {
                throw new Exception("Unknown provider: {$provider}");
            }

            $latencyMs = (int) ((microtime(true) - $startTime) * 1000);

            $inputTokens = $response['usage']['prompt_tokens'];
            $outputTokens = $response['usage']['completion_tokens'];

            $inputCost = ($inputTokens / 1_000_000) * $config['input_cost_per_1m'];
            $outputCost = ($outputTokens / 1_000_000) * $config['output_cost_per_1m'];
            $totalCost = $inputCost + $outputCost;

            return [
                'content' => $response['content'],
                'model' => $model,
                'tokens' => [
                    'input' => $inputTokens,
                    'output' => $outputTokens,
                    'total' => $inputTokens + $outputTokens,
                ],
                'cost' => $totalCost,
                'latency_ms' => $latencyMs,
                'request_id' => $response['request_id'] ?? Str::uuid()->toString(),
            ];
        } catch (Exception $e) {
            Log::error('AI API call failed', [
                'model' => $model,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            throw new Exception("AI API call failed: {$e->getMessage()}");
        }
    }

    /**
     * Track AI API cost for analytics and budget management.
     *
     * @param  string  $model  AI model used
     * @param  int  $inputTokens  Input tokens consumed
     * @param  int  $outputTokens  Output tokens generated
     * @param  float  $cost  Total cost in USD
     * @param  int|null  $contentId  Related content ID
     * @param  int|null  $supplierId  Related supplier ID
     * @param  string|null  $batchId  Related batch ID
     * @param  string  $operationType  Operation type (generation, regeneration, etc.)
     * @return SupplierAiCost Cost tracking record
     */
    public function trackAiCost(
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $cost,
        ?int $contentId = null,
        ?int $supplierId = null,
        ?string $batchId = null,
        string $operationType = 'generation'
    ): SupplierAiCost {
        $config = self::MODEL_CONFIG[$model] ?? [];

        $inputCost = ($inputTokens / 1_000_000) * ($config['input_cost_per_1m'] ?? 0);
        $outputCost = ($outputTokens / 1_000_000) * ($config['output_cost_per_1m'] ?? 0);

        return SupplierAiCost::create([
            'supplier_id' => $supplierId,
            'content_id' => $contentId,
            'batch_id' => $batchId,
            'model' => $model,
            'operation_type' => $operationType,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
        ]);
    }

    /**
     * Validate generated content for quality, readability, and requirements.
     *
     * @param  SupplierAiContent  $content  Content to validate
     * @return SupplierContentValidation Validation results
     */
    public function validateContent(SupplierAiContent $content): SupplierContentValidation
    {
        $qualityScore = $this->calculateQualityScore($content->long_description ?? '');
        $readabilityScore = $this->checkReadability($content->long_description ?? '');

        $combinedText = implode(' ', array_filter([
            $content->generated_name,
            $content->short_description,
            $content->long_description,
        ]));

        $issues = [];
        $suggestions = [];

        if (empty($content->generated_name)) {
            $issues[] = 'Missing product name';
        }

        if (empty($content->long_description)) {
            $issues[] = 'Missing long description';
        }

        if ($content->prompt && $content->prompt->required_sections) {
            foreach ($content->prompt->required_sections as $section) {
                if (! str_contains(strtolower($combinedText), strtolower($section))) {
                    $issues[] = "Missing required section: {$section}";
                }
            }
        }

        if (str_word_count($content->long_description ?? '') < 100) {
            $suggestions[] = 'Description should be at least 100 words';
        }

        if ($qualityScore < self::QUALITY_ACCEPTABLE) {
            $suggestions[] = 'Quality score is below acceptable threshold';
        }

        if ($readabilityScore < self::READABILITY_MODERATE) {
            $suggestions[] = 'Content readability could be improved';
        }

        $validationStatus = empty($issues) && $qualityScore >= self::QUALITY_GOOD
            ? 'passed'
            : (empty($issues) ? 'needs_review' : 'failed');

        return SupplierContentValidation::create([
            'content_id' => $content->id,
            'quality_score' => $qualityScore,
            'readability_score' => $readabilityScore,
            'has_required_sections' => count($issues) === 0,
            'validation_status' => $validationStatus,
            'issues' => $issues,
            'suggestions' => $suggestions,
            'validated_at' => now(),
        ]);
    }

    /**
     * Calculate quality score for content (0-100).
     *
     * @param  string  $content  Content text to evaluate
     * @return float Quality score
     */
    public function calculateQualityScore(string $content): float
    {
        if (empty($content)) {
            return 0.0;
        }

        $score = 50.0;

        $wordCount = str_word_count($content);
        if ($wordCount >= 100) {
            $score += 10;
        }
        if ($wordCount >= 200) {
            $score += 10;
        }

        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count($sentences);

        if ($sentenceCount >= 5) {
            $score += 10;
        }

        $words = str_word_count(strtolower($content), 1);
        $uniqueWords = array_unique($words);
        $uniqueRatio = count($uniqueWords) / max(count($words), 1);

        if ($uniqueRatio >= 0.7) {
            $score += 10;
        }

        if (preg_match('/\b(specifications|features|benefits|warranty|includes)\b/i', $content)) {
            $score += 10;
        }

        return min(100.0, max(0.0, $score));
    }

    /**
     * Check readability score using Flesch Reading Ease formula.
     *
     * @param  string  $content  Content text to evaluate
     * @return float Readability score (0-100, higher is easier)
     */
    public function checkReadability(string $content): float
    {
        if (empty($content)) {
            return 0.0;
        }

        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = max(count($sentences), 1);

        $words = str_word_count($content, 1);
        $wordCount = max(count($words), 1);

        $syllableCount = 0;
        foreach ($words as $word) {
            $syllableCount += $this->countSyllables($word);
        }
        $syllableCount = max($syllableCount, 1);

        $fleschScore = 206.835
            - (1.015 * ($wordCount / $sentenceCount))
            - (84.6 * ($syllableCount / $wordCount));

        return max(0.0, min(100.0, $fleschScore));
    }

    /**
     * Submit content for manual review.
     *
     * @param  SupplierAiContent  $content  Content to submit
     */
    public function submitForReview(SupplierAiContent $content): void
    {
        $content->transitionTo(SupplierAiContent::STATUS_IN_REVIEW);

        Log::info('Content submitted for review', [
            'content_id' => $content->id,
            'supplier_id' => $content->supplier_id,
        ]);
    }

    /**
     * Approve content and mark as validated.
     *
     * @param  SupplierAiContent  $content  Content to approve
     * @param  int  $userId  User ID who approved
     */
    public function approveContent(SupplierAiContent $content, int $userId): void
    {
        $content->validate($userId);

        Log::info('Content approved', [
            'content_id' => $content->id,
            'validated_by' => $userId,
        ]);
    }

    /**
     * Reject content with reason.
     *
     * @param  SupplierAiContent  $content  Content to reject
     * @param  int  $userId  User ID who rejected
     * @param  string  $reason  Rejection reason
     */
    public function rejectContent(SupplierAiContent $content, int $userId, string $reason): void
    {
        $content->reject($reason, $userId);

        Log::info('Content rejected', [
            'content_id' => $content->id,
            'rejected_by' => $userId,
            'reason' => $reason,
        ]);
    }

    /**
     * Render prompt template with variables.
     *
     * @param  SupplierPrompt  $prompt  Prompt template
     * @param  array  $variables  Variable values to replace
     * @return string Rendered prompt text
     */
    public function renderPrompt(SupplierPrompt $prompt, array $variables): string
    {
        return $prompt->render($variables);
    }

    /**
     * Check if daily or monthly budget limits are exceeded.
     *
     * @throws Exception When budget limit is exceeded
     */
    protected function checkBudgetLimits(): void
    {
        if (SupplierAiCost::isDailyBudgetExceeded($this->dailyBudgetLimit)) {
            throw new Exception("Daily AI budget limit of \${$this->dailyBudgetLimit} has been exceeded.");
        }

        if (SupplierAiCost::isMonthlyBudgetExceeded($this->monthlyBudgetLimit)) {
            throw new Exception("Monthly AI budget limit of \${$this->monthlyBudgetLimit} has been exceeded.");
        }
    }

    /**
     * Call OpenAI API.
     *
     * @param  string  $prompt  Prompt text
     * @param  string  $model  Model name
     * @param  int  $maxTokens  Max tokens to generate
     * @param  float  $temperature  Creativity temperature
     * @return array API response
     *
     * @throws Exception When API call fails
     */
    protected function callOpenAi(string $prompt, string $model, int $maxTokens, float $temperature): array
    {
        if (! $this->openaiApiKey) {
            throw new Exception('OpenAI API key not configured');
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->openaiApiKey}",
            'Content-Type' => 'application/json',
        ])
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional product content writer. Generate high-quality, SEO-optimized product descriptions.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

        if (! $response->successful()) {
            throw new Exception("OpenAI API error: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();

        return [
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'usage' => [
                'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
            ],
            'request_id' => $response->header('x-request-id'),
        ];
    }

    /**
     * Call Anthropic (Claude) API.
     *
     * @param  string  $prompt  Prompt text
     * @param  string  $model  Model name
     * @param  int  $maxTokens  Max tokens to generate
     * @param  float  $temperature  Creativity temperature
     * @return array API response
     *
     * @throws Exception When API call fails
     */
    protected function callAnthropic(string $prompt, string $model, int $maxTokens, float $temperature): array
    {
        if (! $this->anthropicApiKey) {
            throw new Exception('Anthropic API key not configured');
        }

        $response = Http::withHeaders([
            'x-api-key' => $this->anthropicApiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])
            ->timeout(120)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if (! $response->successful()) {
            throw new Exception("Anthropic API error: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();

        $content = collect($data['content'] ?? [])
            ->where('type', 'text')
            ->pluck('text')
            ->join("\n");

        return [
            'content' => $content,
            'usage' => [
                'prompt_tokens' => $data['usage']['input_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['output_tokens'] ?? 0,
            ],
            'request_id' => $response->header('request-id'),
        ];
    }

    /**
     * Prepare variables from extraction result for prompt rendering.
     *
     * @param  SupplierExtractionResult  $result  Extraction result
     * @return array Variables array
     */
    protected function prepareVariables(SupplierExtractionResult $result): array
    {
        $data = $result->extracted_data;

        return [
            'product_name' => $data['product_name'] ?? 'Unknown Product',
            'reference' => $result->reference ?? '',
            'ean' => $result->ean ?? '',
            'short_description' => $data['short_description'] ?? '',
            'long_description' => $data['long_description'] ?? '',
            'specifications' => $data['specifications'] ?? [],
            'features' => $data['features'] ?? [],
            'price' => $data['price'] ?? '',
            'brand' => $data['brand'] ?? '',
            'category' => $data['category'] ?? '',
            'supplier' => $result->supplier->label ?? '',
        ];
    }

    /**
     * Prepare variables from existing content for regeneration.
     *
     * @param  SupplierAiContent  $content  Existing content
     * @return array Variables array
     */
    protected function prepareVariablesFromContent(SupplierAiContent $content): array
    {
        $data = $content->source_attributes ?? [];

        return [
            'product_name' => $data['product_name'] ?? $content->generated_name ?? 'Unknown Product',
            'reference' => $content->erp_reference ?? '',
            'ean' => $content->ean ?? '',
            'short_description' => $data['short_description'] ?? $content->short_description ?? '',
            'long_description' => $data['long_description'] ?? $content->long_description ?? '',
            'specifications' => $data['specifications'] ?? [],
            'features' => $data['features'] ?? [],
            'price' => $data['price'] ?? '',
            'brand' => $data['brand'] ?? '',
            'category' => $data['category'] ?? '',
            'supplier' => $content->supplier->label ?? '',
        ];
    }

    /**
     * Parse AI response into structured content fields.
     *
     * @param  string  $response  AI response text
     * @return array Parsed content fields
     */
    protected function parseAiResponse(string $response): array
    {
        $lines = explode("\n", $response);
        $parsed = [
            'name' => null,
            'short_description' => null,
            'long_description' => null,
            'bullet_points' => [],
            'seo_title' => null,
            'seo_description' => null,
            'seo_keywords' => null,
        ];

        $currentSection = null;
        $buffer = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/^(name|title|product name):/i', $line, $matches)) {
                $currentSection = 'name';
                $parsed['name'] = trim(preg_replace('/^(name|title|product name):\s*/i', '', $line));

                continue;
            }

            if (preg_match('/^short description:/i', $line)) {
                $currentSection = 'short_description';

                continue;
            }

            if (preg_match('/^(long description|description):/i', $line)) {
                $currentSection = 'long_description';

                continue;
            }

            if (preg_match('/^(bullet points|features|key features):/i', $line)) {
                $currentSection = 'bullet_points';

                continue;
            }

            if (preg_match('/^seo title:/i', $line)) {
                $currentSection = 'seo_title';
                $parsed['seo_title'] = trim(preg_replace('/^seo title:\s*/i', '', $line));

                continue;
            }

            if (preg_match('/^seo description:/i', $line)) {
                $currentSection = 'seo_description';

                continue;
            }

            if (preg_match('/^(seo keywords|keywords):/i', $line)) {
                $currentSection = 'seo_keywords';
                $parsed['seo_keywords'] = trim(preg_replace('/^(seo keywords|keywords):\s*/i', '', $line));

                continue;
            }

            if ($currentSection === 'bullet_points' && preg_match('/^[-*•]\s*(.+)/', $line, $matches)) {
                $parsed['bullet_points'][] = trim($matches[1]);
            } elseif ($currentSection && ! empty($line)) {
                if (in_array($currentSection, ['short_description', 'long_description', 'seo_description'])) {
                    $buffer[] = $line;
                }
            }

            if ($currentSection === 'short_description' && count($buffer) > 0) {
                $parsed['short_description'] = implode(' ', $buffer);
                $buffer = [];
            }

            if ($currentSection === 'long_description' && count($buffer) > 0) {
                $parsed['long_description'] = implode("\n", $buffer);
                $buffer = [];
            }

            if ($currentSection === 'seo_description' && count($buffer) > 0) {
                $parsed['seo_description'] = implode(' ', $buffer);
                $buffer = [];
            }
        }

        if (! empty($buffer)) {
            if ($currentSection === 'short_description') {
                $parsed['short_description'] = implode(' ', $buffer);
            } elseif ($currentSection === 'long_description') {
                $parsed['long_description'] = implode("\n", $buffer);
            } elseif ($currentSection === 'seo_description') {
                $parsed['seo_description'] = implode(' ', $buffer);
            }
        }

        if (empty($parsed['long_description']) && ! empty($response)) {
            $parsed['long_description'] = $response;
        }

        return $parsed;
    }

    /**
     * Count syllables in a word (approximation for English).
     *
     * @param  string  $word  Word to count
     * @return int Syllable count
     */
    protected function countSyllables(string $word): int
    {
        $word = strtolower($word);
        $word = preg_replace('/[^a-z]/', '', $word);

        if (strlen($word) <= 3) {
            return 1;
        }

        $word = preg_replace('/(?:[^laeiouy]es|ed|[^laeiouy]e)$/', '', $word);
        $word = preg_replace('/^y/', '', $word);

        $syllables = preg_match_all('/[aeiouy]{1,2}/', $word);

        return max(1, $syllables);
    }
}
