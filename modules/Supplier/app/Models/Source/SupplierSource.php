<?php

namespace Modules\Supplier\Models\Source;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierSource extends Model
{
    use HasFactory, HasUid;

    protected $table = 'supplier_sources';

    public const SOURCE_TYPE_WEBSITE = 'website';

    public const SOURCE_TYPE_FTP = 'ftp';

    public const SOURCE_TYPE_FILE = 'file';

    public const SOURCE_TYPE_API = 'api';

    public const TRUST_LEVEL_HIGH = 'high';

    public const TRUST_LEVEL_MEDIUM = 'medium';

    public const TRUST_LEVEL_LOW = 'low';

    protected $fillable = [
        'supplier_id',
        'source_type',
        'label',
        'description',
        'trust_level',
        'usage_notes',
        'priority',
        'is_active',
        'last_accessed_at',
    ];

    protected $casts = [
        'supplier_id' => 'integer',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'last_accessed_at' => 'datetime',
    ];

    /**
     * Get the supplier that owns this source
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get all configuration options for this source
     */
    public function options(): HasMany
    {
        return $this->hasMany(SupplierSourceOption::class, 'source_id');
    }

    /**
     * Get all prompts for this source
     */
    public function prompts(): HasMany
    {
        return $this->hasMany(SupplierPrompt::class, 'source_id');
    }

    /**
     * Get all configurations for this source
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(SupplierSourceConfiguration::class, 'source_id');
    }

    /**
     * Get all transformations for this source
     */
    public function transformations(): HasMany
    {
        return $this->hasMany(SupplierSourceTransformation::class, 'source_id');
    }

    /**
     * Get the monitor for this source
     */
    public function monitor(): HasMany
    {
        return $this->hasMany(SupplierSourceMonitor::class, 'source_id');
    }

    /**
     * Get health history for this source
     */
    public function healthHistory(): HasMany
    {
        return $this->hasMany(SupplierSourceHealthHistory::class, 'source_id');
    }

    /**
     * Scope: Filter active sources
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by source type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('source_type', $type);
    }

    /**
     * Scope: Filter by trust level
     */
    public function scopeWithTrustLevel($query, string $trustLevel)
    {
        return $query->where('trust_level', $trustLevel);
    }

    /**
     * Scope: Order by priority (ascending = highest priority first)
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Scope: Filter by supplier
     */
    public function scopeForSupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Helper: Check if source is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Helper: Check if source is of type website
     */
    public function isWebsite(): bool
    {
        return $this->source_type === self::SOURCE_TYPE_WEBSITE;
    }

    /**
     * Helper: Check if source is of type FTP
     */
    public function isFtp(): bool
    {
        return $this->source_type === self::SOURCE_TYPE_FTP;
    }

    /**
     * Helper: Check if source is of type file
     */
    public function isFile(): bool
    {
        return $this->source_type === self::SOURCE_TYPE_FILE;
    }

    /**
     * Helper: Check if source is of type API
     */
    public function isApi(): bool
    {
        return $this->source_type === self::SOURCE_TYPE_API;
    }

    /**
     * Helper: Get option value by key
     */
    public function getOption(string $key, $default = null)
    {
        $option = $this->options()->where('option_key', $key)->first();

        return $option ? $option->option_value : $default;
    }

    /**
     * Helper: Set option value
     */
    public function setOption(string $key, $value, string $type = 'string', bool $isRequired = false): SupplierSourceOption
    {
        return $this->options()->updateOrCreate(
            ['option_key' => $key],
            [
                'option_value' => $value,
                'option_type' => $type,
                'is_required' => $isRequired,
            ]
        );
    }

    /**
     * Helper: Get all options as key-value array
     */
    public function getOptionsArray(): array
    {
        return $this->options()->pluck('option_value', 'option_key')->toArray();
    }

    /**
     * Helper: Update last accessed timestamp
     */
    public function markAsAccessed(): void
    {
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Helper: Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->label} ({$this->source_type})";
    }
}
