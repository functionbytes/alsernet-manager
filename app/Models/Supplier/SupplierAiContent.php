<?php

namespace App\Models\Supplier;

use App\Library\Traits\HasUid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uid
 * @property int $supplier_id
 * @property int|null $supplier_product_id
 * @property int|null $product_id
 * @property string|null $erp_reference
 * @property string|null $model_id
 * @property string|null $ean
 * @property string $status
 * @property string|null $generated_name
 * @property string|null $short_description
 * @property string|null $long_description
 * @property array|null $bullet_points
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $seo_keywords
 * @property array|null $source_attributes
 * @property array|null $sources_used
 * @property int|null $prompt_id
 * @property array|null $generation_metadata
 * @property string|null $error_message
 * @property int|null $validated_by
 * @property \Illuminate\Support\Carbon|null $validated_at
 * @property string|null $rejection_reason
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $synced_to_erp_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Supplier\Supplier $supplier
 * @property-read \App\Models\Supplier\SupplierPrompt|null $prompt
 * @property-read \App\Models\User|null $validator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supplier\SupplierContentLog> $logs
 * @property-read int|null $logs_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent byStatus(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent forSupplier(int $supplierId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent hasErrors()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent inReview()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent needsValidation()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent uid(string $uid)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent validated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereEan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereErpReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereGeneratedName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereGenerationMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereLongDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent wherePromptId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereSeoDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereSeoKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereSeoTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereSourceAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereSourcesUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereSupplierProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereSyncedToErpAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereValidatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierAiContent whereValidatedBy($value)
 *
 * @mixin \Eloquent
 */
class SupplierAiContent extends Model
{
    use HasFactory, HasUid;

    protected $table = 'supplier_ai_contents';

    public const STATUS_PENDING_GENERATION = 'pending_generation';

    public const STATUS_GENERATING = 'generating';

    public const STATUS_PENDING_VALIDATION = 'pending_validation';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_NEEDS_REVISION = 'needs_revision';

    public const STATUS_VALIDATED = 'validated';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_ERROR_INSUFFICIENT_INFO = 'error_insufficient_info';

    public const STATUS_ERROR_SOURCE_UNAVAILABLE = 'error_source_unavailable';

    public const STATUS_ERROR_GENERATION_FAILED = 'error_generation_failed';

    public const ACTION_CREATED = 'created';

    public const ACTION_GENERATION_STARTED = 'generation_started';

    public const ACTION_GENERATION_COMPLETED = 'generation_completed';

    public const ACTION_GENERATION_FAILED = 'generation_failed';

    public const ACTION_VALIDATED = 'validated';

    public const ACTION_REJECTED = 'rejected';

    public const ACTION_REVISION_REQUESTED = 'revision_requested';

    public const ACTION_EDITED = 'edited';

    public const ACTION_PUBLISHED = 'published';

    public const ACTION_SYNCED_TO_ERP = 'synced_to_erp';

    protected $fillable = [
        'uid',
        'supplier_id',
        'supplier_product_id',
        'product_id',
        'erp_reference',
        'model_id',
        'ean',
        'status',
        'generated_name',
        'short_description',
        'long_description',
        'bullet_points',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'source_attributes',
        'sources_used',
        'prompt_id',
        'generation_metadata',
        'error_message',
        'validated_by',
        'validated_at',
        'rejection_reason',
        'published_at',
        'synced_to_erp_at',
    ];

    protected function casts(): array
    {
        return [
            'bullet_points' => 'array',
            'source_attributes' => 'array',
            'sources_used' => 'array',
            'generation_metadata' => 'array',
            'validated_at' => 'datetime',
            'published_at' => 'datetime',
            'synced_to_erp_at' => 'datetime',
            'supplier_id' => 'integer',
            'supplier_product_id' => 'integer',
            'product_id' => 'integer',
            'prompt_id' => 'integer',
            'validated_by' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $content) {
            if (is_null($content->uid)) {
                $content->uid = (string) Str::ulid();
            }
            if (is_null($content->status)) {
                $content->status = self::STATUS_PENDING_GENERATION;
            }
        });

        static::created(function (self $content) {
            $content->log(self::ACTION_CREATED);
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(SupplierPrompt::class, 'prompt_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SupplierContentLog::class, 'content_id');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING_GENERATION);
    }

    public function scopeNeedsValidation($query)
    {
        return $query->where('status', self::STATUS_PENDING_VALIDATION);
    }

    public function scopeInReview($query)
    {
        return $query->where('status', self::STATUS_IN_REVIEW);
    }

    public function scopeValidated($query)
    {
        return $query->where('status', self::STATUS_VALIDATED);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeHasErrors($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ERROR_INSUFFICIENT_INFO,
            self::STATUS_ERROR_SOURCE_UNAVAILABLE,
            self::STATUS_ERROR_GENERATION_FAILED,
        ]);
    }

    public function scopeForSupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeUid($query, string $uid)
    {
        return $query->where('uid', $uid);
    }

    public function log(
        string $action,
        ?string $previousStatus = null,
        ?string $newStatus = null,
        ?array $details = null,
        ?int $userId = null
    ): SupplierContentLog {
        return $this->logs()->create([
            'action' => $action,
            'previous_status' => $previousStatus ?? $this->getOriginal('status'),
            'new_status' => $newStatus ?? $this->status,
            'user_id' => $userId ?? Auth::id(),
            'details' => $details,
            'ip_address' => request()->ip(),
        ]);
    }

    public function transitionTo(string $newStatus, ?array $extraData = []): bool
    {
        $previousStatus = $this->status;

        $this->update(array_merge(['status' => $newStatus], $extraData));

        $action = $this->getActionFromTransition($previousStatus, $newStatus);
        $this->log($action, $previousStatus, $newStatus);

        return true;
    }

    public function validate(?int $userId = null, ?string $notes = null): bool
    {
        $this->validated_by = $userId ?? Auth::id();
        $this->validated_at = now();
        $this->status = self::STATUS_VALIDATED;
        $this->save();

        $this->log(self::ACTION_VALIDATED, null, null, $notes ? ['notes' => $notes] : null);

        return true;
    }

    public function reject(string $reason, ?int $userId = null): bool
    {
        $this->rejection_reason = $reason;
        $this->status = self::STATUS_REJECTED;
        $this->save();

        $this->log(self::ACTION_REJECTED, null, null, ['reason' => $reason], $userId);

        return true;
    }

    public function requestRevision(string $notes, ?int $userId = null): bool
    {
        $this->status = self::STATUS_NEEDS_REVISION;
        $this->save();

        $this->log(self::ACTION_REVISION_REQUESTED, null, null, ['notes' => $notes], $userId);

        return true;
    }

    public function publish(): bool
    {
        $this->published_at = now();
        $this->status = self::STATUS_PUBLISHED;
        $this->save();

        $this->log(self::ACTION_PUBLISHED);

        return true;
    }

    public function syncToErp(): bool
    {
        $this->synced_to_erp_at = now();
        $this->save();

        $this->log(self::ACTION_SYNCED_TO_ERP);

        return true;
    }

    public function markAsGenerating(): bool
    {
        return $this->transitionTo(self::STATUS_GENERATING);
    }

    public function markAsGenerated(): bool
    {
        return $this->transitionTo(self::STATUS_PENDING_VALIDATION);
    }

    public function markAsFailed(string $errorMessage, string $errorStatus = self::STATUS_ERROR_GENERATION_FAILED): bool
    {
        return $this->transitionTo($errorStatus, ['error_message' => $errorMessage]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_GENERATION;
    }

    public function isGenerating(): bool
    {
        return $this->status === self::STATUS_GENERATING;
    }

    public function isValidated(): bool
    {
        return $this->status === self::STATUS_VALIDATED;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function hasErrors(): bool
    {
        return in_array($this->status, [
            self::STATUS_ERROR_INSUFFICIENT_INFO,
            self::STATUS_ERROR_SOURCE_UNAVAILABLE,
            self::STATUS_ERROR_GENERATION_FAILED,
        ]);
    }

    protected function getActionFromTransition(string $from, string $to): string
    {
        return match ($to) {
            self::STATUS_GENERATING => self::ACTION_GENERATION_STARTED,
            self::STATUS_PENDING_VALIDATION => self::ACTION_GENERATION_COMPLETED,
            self::STATUS_VALIDATED => self::ACTION_VALIDATED,
            self::STATUS_REJECTED => self::ACTION_REJECTED,
            self::STATUS_NEEDS_REVISION => self::ACTION_REVISION_REQUESTED,
            self::STATUS_PUBLISHED => self::ACTION_PUBLISHED,
            self::STATUS_ERROR_GENERATION_FAILED,
            self::STATUS_ERROR_INSUFFICIENT_INFO,
            self::STATUS_ERROR_SOURCE_UNAVAILABLE => self::ACTION_GENERATION_FAILED,
            default => self::ACTION_EDITED,
        };
    }
}
