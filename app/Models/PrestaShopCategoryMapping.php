<?php

namespace App\Models;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Prestashop\Entities\Category as PrestaShopCategory;

/**
 * PrestaShopCategoryMapping Model
 *
 * Tracks bidirectional synchronization status between Laravel and PrestaShop categories
 *
 * @property int $id
 * @property string $uid ULID unique identifier
 * @property int $category_id Laravel category ID
 * @property int $prestashop_category_id PrestaShop id_category
 * @property \Illuminate\Support\Carbon|null $last_synced_at Last successful sync timestamp
 * @property string $sync_direction Sync direction (laravel_to_ps|ps_to_laravel|manual)
 * @property string $sync_status Sync status (pending|in_progress|success|failed|conflict)
 * @property array|null $conflict_data Conflicting data when status is conflict
 * @property string|null $conflict_resolution Resolution strategy (laravel_wins|prestashop_wins|merge|manual|skip)
 * @property \Illuminate\Support\Carbon|null $conflict_detected_at When conflict was detected
 * @property \Illuminate\Support\Carbon|null $conflict_resolved_at When conflict was resolved
 * @property array|null $metadata Additional sync metadata and logs
 * @property string|null $error_message Last error message
 * @property int $sync_attempts Number of sync attempts
 * @property \Illuminate\Support\Carbon|null $last_sync_attempt_at Last attempt timestamp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Category $category
 * @property-read PrestaShopCategory $prestashopCategory
 */
class PrestaShopCategoryMapping extends Model
{
    use HasFactory, HasUid, SoftDeletes;

    protected $table = 'prestashop_category_mappings';

    protected $fillable = [
        'category_id',
        'prestashop_category_id',
        'last_synced_at',
        'sync_direction',
        'sync_status',
        'conflict_data',
        'conflict_resolution',
        'conflict_detected_at',
        'conflict_resolved_at',
        'metadata',
        'error_message',
        'sync_attempts',
        'last_sync_attempt_at',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'prestashop_category_id' => 'integer',
        'last_synced_at' => 'datetime',
        'conflict_detected_at' => 'datetime',
        'conflict_resolved_at' => 'datetime',
        'last_sync_attempt_at' => 'datetime',
        'conflict_data' => 'array',
        'metadata' => 'array',
        'sync_attempts' => 'integer',
    ];

    /**
     * Get the Laravel category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the PrestaShop category
     */
    public function prestashopCategory(): BelongsTo
    {
        return $this->belongsTo(PrestaShopCategory::class, 'prestashop_category_id', 'id_category');
    }

    /**
     * Scope: Filter mappings with conflicts
     */
    public function scopeConflicts($query)
    {
        return $query->where('sync_status', 'conflict');
    }

    /**
     * Scope: Filter failed sync attempts
     */
    public function scopeFailed($query)
    {
        return $query->where('sync_status', 'failed');
    }

    /**
     * Scope: Filter pending syncs
     */
    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    /**
     * Scope: Filter successful syncs
     */
    public function scopeSuccess($query)
    {
        return $query->where('sync_status', 'success');
    }

    /**
     * Scope: Filter in progress syncs
     */
    public function scopeInProgress($query)
    {
        return $query->where('sync_status', 'in_progress');
    }

    /**
     * Scope: Filter by sync direction
     */
    public function scopeDirection($query, string $direction)
    {
        return $query->where('sync_direction', $direction);
    }

    /**
     * Scope: Filter mappings that need attention (conflicts or failures)
     */
    public function scopeNeedsAttention($query)
    {
        return $query->whereIn('sync_status', ['conflict', 'failed']);
    }

    /**
     * Scope: Filter recent syncs (last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('last_synced_at', '>=', now()->subDay());
    }

    /**
     * Scope: Order by most recent sync attempt
     */
    public function scopeLatestAttempts($query)
    {
        return $query->orderBy('last_sync_attempt_at', 'desc');
    }

    /**
     * Check if mapping has unresolved conflict
     */
    public function hasConflict(): bool
    {
        return $this->sync_status === 'conflict' && is_null($this->conflict_resolved_at);
    }

    /**
     * Check if sync has failed
     */
    public function hasFailed(): bool
    {
        return $this->sync_status === 'failed';
    }

    /**
     * Check if sync is currently in progress
     */
    public function isInProgress(): bool
    {
        return $this->sync_status === 'in_progress';
    }

    /**
     * Check if sync was successful
     */
    public function isSuccessful(): bool
    {
        return $this->sync_status === 'success';
    }

    /**
     * Mark sync as successful
     */
    public function markAsSuccess(): void
    {
        $this->update([
            'sync_status' => 'success',
            'last_synced_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark sync as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'sync_status' => 'failed',
            'error_message' => $errorMessage,
            'sync_attempts' => $this->sync_attempts + 1,
            'last_sync_attempt_at' => now(),
        ]);
    }

    /**
     * Mark sync as conflict
     */
    public function markAsConflict(array $conflictData): void
    {
        $this->update([
            'sync_status' => 'conflict',
            'conflict_data' => $conflictData,
            'conflict_detected_at' => now(),
        ]);
    }

    /**
     * Mark conflict as resolved
     */
    public function resolveConflict(string $resolution): void
    {
        $this->update([
            'conflict_resolution' => $resolution,
            'conflict_resolved_at' => now(),
            'sync_status' => 'success',
        ]);
    }

    /**
     * Reset sync status to pending
     */
    public function resetToPending(): void
    {
        $this->update([
            'sync_status' => 'pending',
            'error_message' => null,
            'conflict_data' => null,
            'conflict_resolution' => null,
            'conflict_detected_at' => null,
            'conflict_resolved_at' => null,
        ]);
    }
}
