<?php

namespace Modules\Supplier\Entities;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SupplierSport Entity
 *
 * Mirror table for Oracle DEPORTE_CL (Sports/Top-level categories)
 *
 * @property int $id
 * @property string $uid ULID unique identifier
 * @property int $erp_sport_id Oracle IDDEPORTE_CL
 * @property string|null $code Sport code
 * @property string $name Sport name
 * @property string|null $short_name Short name
 * @property bool $is_active Active status
 * @property array|null $metadata Additional metadata
 * @property \Carbon\Carbon|null $erp_created_at Oracle creation date
 * @property \Carbon\Carbon|null $erp_updated_at Oracle update date
 * @property \Carbon\Carbon|null $erp_deleted_at Oracle soft delete date
 * @property \Carbon\Carbon|null $last_synced_at Last sync timestamp
 */
class SupplierSport extends Model
{
    use HasFactory, HasUid, SoftDeletes;

    protected $table = 'supplier_sports';

    protected $fillable = [
        'erp_sport_id',
        'code',
        'name',
        'short_name',
        'is_active',
        'metadata',
        'erp_created_at',
        'erp_updated_at',
        'erp_deleted_at',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'erp_created_at' => 'datetime',
        'erp_updated_at' => 'datetime',
        'erp_deleted_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get all categories for this sport
     */
    public function categories(): HasMany
    {
        return $this->hasMany(SupplierErpCategory::class, 'sport_id');
    }

    /**
     * Get only active categories
     */
    public function activeCategories(): HasMany
    {
        return $this->categories()->where('is_active', true);
    }

    /**
     * Scope: Active sports only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By Oracle ID
     */
    public function scopeByErpId($query, int $erpId)
    {
        return $query->where('erp_sport_id', $erpId);
    }

    /**
     * Check if needs sync (Oracle updated after last sync)
     */
    public function needsSync(): bool
    {
        if (!$this->last_synced_at) {
            return true;
        }

        return $this->erp_updated_at && $this->erp_updated_at->isAfter($this->last_synced_at);
    }
}
