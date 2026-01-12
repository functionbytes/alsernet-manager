<?php

namespace Modules\Supplier\Entities;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SupplierProductPrice Entity
 *
 * Mirror table for Oracle ARTIPROV_TARIFAPRO (Price history)
 */
class SupplierProductPrice extends Model
{
    use HasFactory, HasUid, SoftDeletes;

    protected $table = 'supplier_product_prices';

    protected $fillable = [
        'erp_price_id',
        'provider_product_id',
        'erp_artiprov_id',
        'effective_date',
        'cost',
        'discount1',
        'discount2',
        'final_cost',
        'is_active',
        'is_current',
        'metadata',
        'erp_created_at',
        'erp_updated_at',
        'erp_deleted_at',
        'last_synced_at',
    ];

    protected $casts = [
        'cost' => 'decimal:4',
        'discount1' => 'decimal:2',
        'discount2' => 'decimal:2',
        'final_cost' => 'decimal:4',
        'is_active' => 'boolean',
        'is_current' => 'boolean',
        'metadata' => 'array',
        'effective_date' => 'date',
        'erp_created_at' => 'datetime',
        'erp_updated_at' => 'datetime',
        'erp_deleted_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function providerProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProviderProduct::class, 'provider_product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeByErpId($query, int $erpId)
    {
        return $query->where('erp_price_id', $erpId);
    }

    public function scopeForProviderProduct($query, int $providerProductId)
    {
        return $query->where('provider_product_id', $providerProductId);
    }

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effective_date', '<=', $date)
            ->where('is_active', true);
    }

    public function needsSync(): bool
    {
        if (!$this->last_synced_at) {
            return true;
        }

        return $this->erp_updated_at && $this->erp_updated_at->isAfter($this->last_synced_at);
    }

    /**
     * Calculate final cost after discounts
     */
    public function calculateFinalCost(): float
    {
        if (!$this->cost) {
            return 0;
        }

        $cost = $this->cost;

        if ($this->discount1) {
            $cost -= ($cost * $this->discount1 / 100);
        }

        if ($this->discount2) {
            $cost -= ($cost * $this->discount2 / 100);
        }

        return round($cost, 4);
    }

    /**
     * Mark this price as current and unmark others
     */
    public function markAsCurrent(): void
    {
        // Unmark all other prices for this provider-product
        self::where('provider_product_id', $this->provider_product_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        // Mark this one as current
        $this->is_current = true;
        $this->save();
    }
}
