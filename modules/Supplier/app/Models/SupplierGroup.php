<?php

namespace Modules\Supplier\Models;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierGroup extends Model
{
    use HasFactory, HasUid, SoftDeletes;

    protected $table = 'supplier_groups';

    protected $fillable = [
        'erp_group_id',
        'subfamily_id',
        'erp_subfamily_id',
        'name',
        'short_name',
        'prefix',
        'next_number',
        'is_active',
        'exclude_store_request',
        'require_serial_number',
        'intrastat',
        'metadata',
        'erp_created_at',
        'erp_updated_at',
        'erp_deleted_at',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'exclude_store_request' => 'boolean',
        'require_serial_number' => 'boolean',
        'metadata' => 'array',
        'erp_created_at' => 'datetime',
        'erp_updated_at' => 'datetime',
        'erp_deleted_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function subfamily(): BelongsTo
    {
        return $this->belongsTo(SupplierSubfamily::class, 'subfamily_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(SupplierProduct::class, 'group_id');
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByErpId($query, int $erpId)
    {
        return $query->where('erp_group_id', $erpId);
    }

    public function needsSync(): bool
    {
        if (! $this->last_synced_at) {
            return true;
        }

        return $this->erp_updated_at && $this->erp_updated_at->isAfter($this->last_synced_at);
    }

    /**
     * Get full category path (Sport > Category > Family > Subfamily > Group)
     */
    public function getFullPathAttribute(): string
    {
        $path = [];

        if ($this->subfamily) {
            if ($this->subfamily->family) {
                if ($this->subfamily->family->category) {
                    if ($this->subfamily->family->category->sport) {
                        $path[] = $this->subfamily->family->category->sport->name;
                    }
                    $path[] = $this->subfamily->family->category->name;
                }
                $path[] = $this->subfamily->family->name;
            }
            $path[] = $this->subfamily->name;
        }
        $path[] = $this->name;

        return implode(' > ', $path);
    }
}
