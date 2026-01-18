<?php

namespace Modules\Supplier\Models;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SupplierProviderProduct Entity
 *
 * Mirror table for Oracle ARTIPROV (Provider-Product relationships)
 */
class SupplierProviderProduct extends Model
{
    use HasFactory, HasUid, SoftDeletes;

    protected $table = 'supplier_provider_products';

    protected $fillable = [
        'erp_artiprov_id',
        'provider_id',
        'product_id',
        'erp_provider_id',
        'erp_product_id',
        'provider_code',
        'provider_code_alt',
        'provider_name',
        'ean13',
        'upc',
        'current_cost',
        'discount1',
        'discount2',
        'final_cost',
        'price_updated_at',
        'is_active',
        'is_default',
        'metadata',
        'erp_created_at',
        'erp_updated_at',
        'erp_deleted_at',
        'last_synced_at',
    ];

    protected $casts = [
        'current_cost' => 'decimal:4',
        'discount1' => 'decimal:2',
        'discount2' => 'decimal:2',
        'final_cost' => 'decimal:4',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'metadata' => 'array',
        'price_updated_at' => 'datetime',
        'erp_created_at' => 'datetime',
        'erp_updated_at' => 'datetime',
        'erp_deleted_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(SupplierErpProvider::class, 'provider_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id');
    }

    /**
     * Get all price history entries
     */
    public function prices(): HasMany
    {
        return $this->hasMany(SupplierProductPrice::class, 'provider_product_id');
    }

    /**
     * Get current active price
     */
    public function currentPrice()
    {
        return $this->prices()
            ->where('is_active', true)
            ->where('is_current', true)
            ->orderBy('effective_date', 'desc')
            ->first();
    }

    /**
     * Get price history ordered by date
     */
    public function priceHistory()
    {
        return $this->prices()
            ->orderBy('effective_date', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByErpId($query, int $erpId)
    {
        return $query->where('erp_artiprov_id', $erpId);
    }

    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function needsSync(): bool
    {
        if (! $this->last_synced_at) {
            return true;
        }

        return $this->erp_updated_at && $this->erp_updated_at->isAfter($this->last_synced_at);
    }

    /**
     * Calculate final cost after applying discounts
     */
    public function calculateFinalCost(): float
    {
        if (! $this->current_cost) {
            return 0;
        }

        $cost = $this->current_cost;

        // Apply discount 1
        if ($this->discount1) {
            $cost -= ($cost * $this->discount1 / 100);
        }

        // Apply discount 2
        if ($this->discount2) {
            $cost -= ($cost * $this->discount2 / 100);
        }

        return round($cost, 4);
    }

    /**
     * Update final cost based on discounts
     */
    public function updateFinalCost(): void
    {
        $this->final_cost = $this->calculateFinalCost();
        $this->save();
    }
}
