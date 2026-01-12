<?php

namespace Modules\Supplier\Entities;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SupplierErpCategory Entity
 *
 * Mirror table for Oracle CATEGORIA_CL (Categories)
 */
class SupplierErpCategory extends Model
{
    use HasFactory, HasUid, SoftDeletes;

    protected $table = 'supplier_erp_categories';

    protected $fillable = [
        'erp_category_id',
        'sport_id',
        'erp_sport_id',
        'name',
        'short_name',
        'is_active',
        'show_in_stock_report',
        'metadata',
        'erp_created_at',
        'erp_updated_at',
        'erp_deleted_at',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_stock_report' => 'boolean',
        'metadata' => 'array',
        'erp_created_at' => 'datetime',
        'erp_updated_at' => 'datetime',
        'erp_deleted_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(SupplierSport::class, 'sport_id');
    }

    public function families(): HasMany
    {
        return $this->hasMany(SupplierFamily::class, 'category_id');
    }

    public function activeFamilies(): HasMany
    {
        return $this->families()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByErpId($query, int $erpId)
    {
        return $query->where('erp_category_id', $erpId);
    }

    public function needsSync(): bool
    {
        if (!$this->last_synced_at) {
            return true;
        }

        return $this->erp_updated_at && $this->erp_updated_at->isAfter($this->last_synced_at);
    }
}
