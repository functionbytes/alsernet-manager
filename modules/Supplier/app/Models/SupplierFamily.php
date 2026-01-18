<?php

namespace Modules\Supplier\Models;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierFamily extends Model
{
    use HasFactory, HasUid, SoftDeletes;

    protected $table = 'supplier_families';

    protected $fillable = [
        'erp_family_id',
        'category_id',
        'erp_category_id',
        'name',
        'short_name',
        'is_active',
        'is_weapons',
        'is_blank_weapons',
        'is_cartridges',
        'metadata',
        'erp_created_at',
        'erp_updated_at',
        'erp_deleted_at',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_weapons' => 'boolean',
        'is_blank_weapons' => 'boolean',
        'is_cartridges' => 'boolean',
        'metadata' => 'array',
        'erp_created_at' => 'datetime',
        'erp_updated_at' => 'datetime',
        'erp_deleted_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupplierErpCategory::class, 'category_id');
    }

    public function subfamilies(): HasMany
    {
        return $this->hasMany(SupplierSubfamily::class, 'family_id');
    }

    public function activeSubfamilies(): HasMany
    {
        return $this->subfamilies()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByErpId($query, int $erpId)
    {
        return $query->where('erp_family_id', $erpId);
    }

    public function needsSync(): bool
    {
        if (! $this->last_synced_at) {
            return true;
        }

        return $this->erp_updated_at && $this->erp_updated_at->isAfter($this->last_synced_at);
    }
}
