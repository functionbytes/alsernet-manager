<?php

namespace Modules\Supplier\Models;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierErpProvider extends Model
{
    use HasFactory, HasUid, SoftDeletes;

    protected $table = 'supplier_erp_providers';

    protected $fillable = [
        'erp_provider_id',
        'code',
        'name',
        'tax_id',
        'contact_person',
        'email',
        'phone',
        'phone_alt',
        'fax',
        'website',
        'street',
        'street_number',
        'city',
        'postal_code',
        'province',
        'country',
        'erp_country_id',
        'shipping_cost',
        'discount_percent',
        'partial_delivery_days',
        'partial_delivery_percent',
        'is_active',
        'is_visible',
        'is_creditor',
        'notes',
        'shipping_notes',
        'supplier_id',
        'metadata',
        'erp_created_at',
        'erp_updated_at',
        'erp_deleted_at',
        'last_synced_at',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'partial_delivery_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'is_creditor' => 'boolean',
        'metadata' => 'array',
        'erp_created_at' => 'datetime',
        'erp_updated_at' => 'datetime',
        'erp_deleted_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Optional link to main Supplier entity
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get all provider-product relationships
     */
    public function providerProducts(): HasMany
    {
        return $this->hasMany(SupplierProviderProduct::class, 'provider_id');
    }

    /**
     * Get only active provider-products
     */
    public function activeProviderProducts(): HasMany
    {
        return $this->providerProducts()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByErpId($query, int $erpId)
    {
        return $query->where('erp_provider_id', $erpId);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
                ->orWhere('code', 'ILIKE', "%{$search}%")
                ->orWhere('tax_id', 'ILIKE', "%{$search}%");
        });
    }

    public function needsSync(): bool
    {
        if (! $this->last_synced_at) {
            return true;
        }

        return $this->erp_updated_at && $this->erp_updated_at->isAfter($this->last_synced_at);
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street.($this->street_number ? ' '.$this->street_number : ''),
            $this->city,
            $this->postal_code,
            $this->province,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}
