<?php

namespace Modules\Supplier\Models;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Group related sync operations together
 *
 * Represents a batch of synchronization operations that should be processed together.
 * Supports batch-level retry logic, progress tracking, and grouping of related operations.
 *
 * @property int $id
 * @property string $uid ULID unique identifier
 * @property int|null $supplier_id Associated supplier ID
 * @property string $batch_name Human-readable batch name
 * @property string $sync_type Type of sync: 'product', 'category', 'price', 'provider'
 * @property string $status Status: 'pending', 'running', 'completed', 'failed', 'cancelled'
 * @property string $priority Priority level: 'low', 'normal', 'high', 'urgent'
 * @property int $batch_size Number of items per batch iteration
 * @property int $total_batches Total number of sub-batches
 * @property int $processed_batches Completed sub-batches
 * @property int $total_items Total items in this batch
 * @property int $processed_items Successfully processed items
 * @property int $failed_items Failed items
 * @property int $retry_attempt Current retry attempt
 * @property int $max_retries Maximum retry attempts allowed
 * @property \Carbon\Carbon|null $started_at When batch started
 * @property \Carbon\Carbon|null $completed_at When batch completed
 * @property \Carbon\Carbon|null $last_retry_at Last retry timestamp
 * @property float|null $duration_seconds Total execution time
 * @property string|null $triggered_by Trigger source: 'manual', 'scheduled', 'webhook'
 * @property string|null $trigger_details Additional trigger information
 * @property array|null $filter_criteria Filter criteria applied to batch
 * @property array|null $metadata Additional metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SupplierSyncBatch extends Model
{
    use HasFactory, HasUid;

    protected $table = 'supplier_sync_batches';

    protected $fillable = [
        'uid',
        'supplier_id',
        'batch_name',
        'sync_type',
        'status',
        'priority',
        'batch_size',
        'total_batches',
        'processed_batches',
        'total_items',
        'processed_items',
        'failed_items',
        'retry_attempt',
        'max_retries',
        'started_at',
        'completed_at',
        'last_retry_at',
        'duration_seconds',
        'triggered_by',
        'trigger_details',
        'filter_criteria',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'batch_size' => 'integer',
            'total_batches' => 'integer',
            'processed_batches' => 'integer',
            'total_items' => 'integer',
            'processed_items' => 'integer',
            'failed_items' => 'integer',
            'retry_attempt' => 'integer',
            'max_retries' => 'integer',
            'duration_seconds' => 'float',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_retry_at' => 'datetime',
            'filter_criteria' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the associated supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get all sync logs for this batch
     */
    public function logs(): HasMany
    {
        return $this->hasMany(SupplierSyncLog::class, 'batch_id');
    }

    /**
     * Get all failures in this batch
     */
    public function failures(): HasMany
    {
        return $this->hasMany(SupplierSyncFailure::class, 'batch_id');
    }

    /**
     * Get all sync statuses for this batch
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(SupplierSyncStatus::class, 'batch_id');
    }

    /**
     * Calculate overall progress percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_items === 0) {
            return 0.0;
        }

        return round(($this->processed_items / $this->total_items) * 100, 2);
    }

    /**
     * Calculate batch progress percentage
     */
    public function getBatchProgressPercentageAttribute(): float
    {
        if ($this->total_batches === 0) {
            return 0.0;
        }

        return round(($this->processed_batches / $this->total_batches) * 100, 2);
    }

    /**
     * Calculate success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        $processed = $this->processed_items + $this->failed_items;

        if ($processed === 0) {
            return 0.0;
        }

        return round(($this->processed_items / $processed) * 100, 2);
    }

    /**
     * Calculate remaining items
     */
    public function getRemainingItemsAttribute(): int
    {
        return $this->total_items - $this->processed_items - $this->failed_items;
    }

    /**
     * Check if batch is currently running
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if batch is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if batch failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if batch was cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if batch can be retried
     */
    public function canRetry(): bool
    {
        return $this->retry_attempt < $this->max_retries &&
               in_array($this->status, ['failed', 'pending']);
    }

    /**
     * Mark batch as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark batch as completed
     */
    public function markAsCompleted(): void
    {
        $completedAt = now();
        $startedAt = $this->started_at;

        $this->update([
            'status' => 'completed',
            'completed_at' => $completedAt,
            'duration_seconds' => $startedAt ? $completedAt->diffInSeconds($startedAt) : null,
        ]);
    }

    /**
     * Mark batch as failed
     */
    public function markAsFailed(): void
    {
        $completedAt = now();
        $startedAt = $this->started_at;

        $this->update([
            'status' => 'failed',
            'completed_at' => $completedAt,
            'duration_seconds' => $startedAt ? $completedAt->diffInSeconds($startedAt) : null,
        ]);
    }

    /**
     * Mark batch as cancelled
     */
    public function markAsCancelled(): void
    {
        $completedAt = now();
        $startedAt = $this->started_at;

        $this->update([
            'status' => 'cancelled',
            'completed_at' => $completedAt,
            'duration_seconds' => $startedAt ? $completedAt->diffInSeconds($startedAt) : null,
        ]);
    }

    /**
     * Increment processed items
     */
    public function incrementProcessedItems(int $count = 1): void
    {
        $this->increment('processed_items', $count);
    }

    /**
     * Increment failed items
     */
    public function incrementFailedItems(int $count = 1): void
    {
        $this->increment('failed_items', $count);
    }

    /**
     * Increment processed batches
     */
    public function incrementProcessedBatches(int $count = 1): void
    {
        $this->increment('processed_batches', $count);
    }

    /**
     * Increment retry attempt
     */
    public function incrementRetryAttempt(): void
    {
        $this->update([
            'retry_attempt' => $this->retry_attempt + 1,
            'last_retry_at' => now(),
        ]);
    }

    /**
     * Scope: Get pending batches
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get running batches
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope: Get completed batches
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Get failed batches
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get batches by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('sync_type', $type);
    }

    /**
     * Scope: Get batches by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Get high priority batches
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high')->orWhere('priority', 'urgent');
    }

    /**
     * Scope: Get retryable batches
     */
    public function scopeRetryable($query)
    {
        return $query->where('retry_attempt', '<', $this->max_retries)
            ->whereIn('status', ['failed', 'pending']);
    }

    /**
     * Scope: Get recent batches
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get human-readable sync type name
     */
    public function getSyncTypeNameAttribute(): string
    {
        return match ($this->sync_type) {
            'product' => 'Producto',
            'category' => 'Categoría',
            'price' => 'Precio',
            'provider' => 'Proveedor',
            default => ucfirst($this->sync_type),
        };
    }

    /**
     * Get human-readable status name
     */
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'running' => 'En Progreso',
            'completed' => 'Completado',
            'failed' => 'Fallido',
            'cancelled' => 'Cancelado',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get human-readable priority name
     */
    public function getPriorityNameAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Baja',
            'normal' => 'Normal',
            'high' => 'Alta',
            'urgent' => 'Urgente',
            default => ucfirst($this->priority),
        };
    }
}
