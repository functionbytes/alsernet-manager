<?php

namespace Modules\Supplier\Entities;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Supplier\Entities\Supplier;

/**
 * SupplierProduct Entity
 *
 * Mirror table for Oracle ARTICULO (Products/Articles)
 */
class SupplierProduct extends Model
{
    use HasFactory, HasUid, SoftDeletes;

    protected $table = 'supplier_products';

    protected $fillable = [
        'erp_product_id',
        'group_id',
        'erp_group_id',
        'supplier_id',
        'code',
        'barcode',
        'name',
        'reference',
        'erp_brand_id',
        'brand_name',
        'erp_model_id',
        'model_name',
        'average_cost',
        'last_purchase_cost',
        'last_purchase_date',
        'recommended_price',
        'length',
        'width',
        'height',
        'weight',
        'units_per_box',
        'max_serve',
        'available_from',
        'allow_reservation',
        'auto_availability',
        'is_active',
        'is_external',
        'is_second_hand',
        'requires_serial',
        'centralized_purchase',
        'image_path',
        'notes',
        'purchase_notes',
        'metadata',
        'erp_created_at',
        'erp_updated_at',
        'erp_deleted_at',
        'last_synced_at',
    ];

    protected $casts = [
        'average_cost' => 'decimal:4',
        'last_purchase_cost' => 'decimal:4',
        'recommended_price' => 'decimal:4',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'weight' => 'decimal:3',
        'is_active' => 'boolean',
        'is_external' => 'boolean',
        'is_second_hand' => 'boolean',
        'requires_serial' => 'boolean',
        'allow_reservation' => 'boolean',
        'auto_availability' => 'boolean',
        'centralized_purchase' => 'boolean',
        'metadata' => 'array',
        'last_purchase_date' => 'datetime',
        'available_from' => 'datetime',
        'erp_created_at' => 'datetime',
        'erp_updated_at' => 'datetime',
        'erp_deleted_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SupplierGroup::class, 'group_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get all provider relationships for this product
     */
    public function providerProducts(): HasMany
    {
        return $this->hasMany(SupplierProviderProduct::class, 'product_id');
    }

    /**
     * Get only active provider relationships
     */
    public function activeProviderProducts(): HasMany
    {
        return $this->providerProducts()->where('is_active', true);
    }

    /**
     * Get the default provider for this product
     */
    public function defaultProviderProduct()
    {
        return $this->providerProducts()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByErpId($query, int $erpId)
    {
        return $query->where('erp_product_id', $erpId);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeByBarcode($query, string $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
                ->orWhere('code', 'ILIKE', "%{$search}%")
                ->orWhere('barcode', 'ILIKE', "%{$search}%")
                ->orWhere('reference', 'ILIKE', "%{$search}%");
        });
    }

    public function needsSync(): bool
    {
        if (!$this->last_synced_at) {
            return true;
        }

        return $this->erp_updated_at && $this->erp_updated_at->isAfter($this->last_synced_at);
    }

    /**
     * Get full category hierarchy path
     */
    public function getCategoryPathAttribute(): ?string
    {
        return $this->group?->full_path;
    }

    /**
     * Calculate final cost after discounts from default provider
     */
    public function getBestPriceAttribute(): ?float
    {
        $defaultProvider = $this->defaultProviderProduct();

        if ($defaultProvider && $defaultProvider->final_cost) {
            return $defaultProvider->final_cost;
        }

        return $this->last_purchase_cost;
    }
}
