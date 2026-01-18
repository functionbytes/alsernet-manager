<?php

namespace Modules\Supplier\Models;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SupplierSubfamily Entity
 *
 * Mirror table for Oracle SUBFAMILIA_CL (Subfamilies)
 */
class SupplierSubfamily extends Model
{
    use HasFactory, HasUid, SoftDeletes;

    protected $table = 'supplier_subfamilies';

    protected $fillable = [
        'erp_subfamily_id',
        'family_id',
        'erp_family_id',
        'name',
        'short_name',
        'is_active',
        'is_ammunition',
        'is_metal_ammunition',
        'show_lots',
        'metadata',
        'erp_created_at',
        'erp_updated_at',
        'erp_deleted_at',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_ammunition' => 'boolean',
        'is_metal_ammunition' => 'boolean',
        'show_lots' => 'boolean',
        'metadata' => 'array',
        'erp_created_at' => 'datetime',
        'erp_updated_at' => 'datetime',
        'erp_deleted_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(SupplierFamily::class, 'family_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(SupplierGroup::class, 'subfamily_id');
    }

    public function activeGroups(): HasMany
    {
        return $this->groups()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByErpId($query, int $erpId)
    {
        return $query->where('erp_subfamily_id', $erpId);
    }

    public function needsSync(): bool
    {
        if (! $this->last_synced_at) {
            return true;
        }

        return $this->erp_updated_at && $this->erp_updated_at->isAfter($this->last_synced_at);
    }
}
