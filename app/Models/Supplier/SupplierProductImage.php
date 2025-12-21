<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SupplierProductImage extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'supplier_product_images';

    /**
     * Disable updated_at timestamp (only created_at)
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'extraction_result_id',
        'supplier_id',
        'reference',
        'source_url',
        'source_hash',
        'local_path',
        'cdn_url',
        'original_width',
        'original_height',
        'processed_width',
        'processed_height',
        'file_size_bytes',
        'mime_type',
        'variants',
        'status',
        'error_message',
        'quality_score',
        'is_primary',
        'position',
        'downloaded_at',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'extraction_result_id' => 'integer',
            'supplier_id' => 'integer',
            'original_width' => 'integer',
            'original_height' => 'integer',
            'processed_width' => 'integer',
            'processed_height' => 'integer',
            'file_size_bytes' => 'integer',
            'variants' => 'array',
            'quality_score' => 'decimal:2',
            'is_primary' => 'boolean',
            'position' => 'integer',
            'downloaded_at' => 'datetime',
            'processed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the extraction result that owns this image.
     */
    public function extractionResult(): BelongsTo
    {
        return $this->belongsTo(SupplierExtractionResult::class, 'extraction_result_id');
    }

    /**
     * Get the supplier that owns this image.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Scope to filter by supplier.
     */
    public function scopeBySupplier(Builder $query, int $supplierId): Builder
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope to filter by reference.
     */
    public function scopeByReference(Builder $query, string $reference): Builder
    {
        return $query->where('reference', $reference);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending images.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter downloading images.
     */
    public function scopeDownloading(Builder $query): Builder
    {
        return $query->where('status', 'downloading');
    }

    /**
     * Scope to filter processing images.
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope to filter completed images.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter failed images.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to filter primary images.
     */
    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to order by position.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }

    /**
     * Scope to filter images by quality score.
     */
    public function scopeMinQuality(Builder $query, float $minScore): Builder
    {
        return $query->where('quality_score', '>=', $minScore);
    }

    /**
     * Check if image is pending processing.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if image is being downloaded.
     */
    public function isDownloading(): bool
    {
        return $this->status === 'downloading';
    }

    /**
     * Check if image is being processed.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if image processing is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if image processing failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get file size in KB.
     */
    public function getFileSizeInKB(): ?float
    {
        if (is_null($this->file_size_bytes)) {
            return null;
        }

        return round($this->file_size_bytes / 1024, 2);
    }

    /**
     * Get file size in MB.
     */
    public function getFileSizeInMB(): ?float
    {
        if (is_null($this->file_size_bytes)) {
            return null;
        }

        return round($this->file_size_bytes / (1024 * 1024), 2);
    }

    /**
     * Get aspect ratio.
     */
    public function getAspectRatio(): ?float
    {
        if (is_null($this->processed_width) || is_null($this->processed_height) || $this->processed_height === 0) {
            return null;
        }

        return round($this->processed_width / $this->processed_height, 2);
    }

    /**
     * Get variant URL by size.
     */
    public function getVariant(string $size): ?string
    {
        return $this->variants[$size] ?? null;
    }

    /**
     * Get thumbnail URL.
     */
    public function getThumbnailUrl(): ?string
    {
        return $this->getVariant('thumb') ?? $this->cdn_url;
    }

    /**
     * Get medium size URL.
     */
    public function getMediumUrl(): ?string
    {
        return $this->getVariant('medium') ?? $this->cdn_url;
    }

    /**
     * Get large size URL.
     */
    public function getLargeUrl(): ?string
    {
        return $this->getVariant('large') ?? $this->cdn_url;
    }

    /**
     * Get processing duration in seconds.
     */
    public function getProcessingDuration(): ?int
    {
        if (! $this->downloaded_at || ! $this->processed_at) {
            return null;
        }

        return $this->downloaded_at->diffInSeconds($this->processed_at);
    }

    /**
     * Check if image has high quality.
     */
    public function isHighQuality(): bool
    {
        return ! is_null($this->quality_score) && $this->quality_score >= 80;
    }

    /**
     * Check if image has low quality.
     */
    public function isLowQuality(): bool
    {
        return ! is_null($this->quality_score) && $this->quality_score < 50;
    }

    /**
     * Mark image as primary.
     */
    public function markAsPrimary(): bool
    {
        // Unset other primary images for same reference
        if ($this->reference) {
            self::where('supplier_id', $this->supplier_id)
                ->where('reference', $this->reference)
                ->where('id', '!=', $this->id)
                ->update(['is_primary' => false]);
        }

        $this->is_primary = true;

        return $this->save();
    }

    /**
     * Delete image files from storage.
     */
    public function deleteFiles(): bool
    {
        $deleted = true;

        // Delete local file
        if ($this->local_path && Storage::exists($this->local_path)) {
            $deleted = Storage::delete($this->local_path) && $deleted;
        }

        // Delete variants
        if ($this->variants && is_array($this->variants)) {
            foreach ($this->variants as $path) {
                if (is_string($path) && Storage::exists($path)) {
                    $deleted = Storage::delete($path) && $deleted;
                }
            }
        }

        return $deleted;
    }

    /**
     * Get image summary for display.
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'is_primary' => $this->is_primary,
            'position' => $this->position,
            'url' => $this->cdn_url,
            'thumbnail' => $this->getThumbnailUrl(),
            'dimensions' => $this->processed_width && $this->processed_height
                ? "{$this->processed_width}x{$this->processed_height}"
                : null,
            'size_kb' => $this->getFileSizeInKB(),
            'quality_score' => $this->quality_score,
            'processing_duration' => $this->getProcessingDuration(),
        ];
    }

    /**
     * Boot the model and register event handlers.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Delete associated files when model is deleted
        static::deleting(function ($image) {
            $image->deleteFiles();
        });
    }
}
