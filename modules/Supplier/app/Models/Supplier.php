<?php

namespace Modules\Supplier\Models;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Catalog\Models\Category;
use Modules\Supplier\Models\Prompt\SupplierPrompt;
use Modules\Supplier\Models\Source\SupplierSource;

class Supplier extends Model
{
    use HasFactory, HasUid;

    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'code',
        'erp_id',
        'supplier_id',
        'website',
        'description',
        'contact_email',
        'contact_phone',
        'priority',
        'content_type',
        'api_rate_limit',
        'api_rate_period',
        'metadata',
        'config',
        'is_active',
        'last_sync_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'erp_id' => 'integer',
        'supplier_id' => 'integer',
        'priority' => 'integer',
        'api_rate_limit' => 'integer',
        'metadata' => 'array',
        'config' => 'array',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Get all data sources for this supplier
     */
    public function sources(): HasMany
    {
        return $this->hasMany(SupplierSource::class, 'supplier_id');
    }

    /**
     * Get only active sources for this supplier
     */
    public function activeSources(): HasMany
    {
        return $this->sources()->where('is_active', true);
    }

    /**
     * Get all prompts for this supplier
     */
    public function prompts(): HasMany
    {
        return $this->hasMany(SupplierPrompt::class, 'supplier_id');
    }

    /**
     * Get all AI-generated contents for this supplier
     */
    public function contents(): HasMany
    {
        return $this->hasMany(SupplierAiContent::class, 'supplier_id');
    }

    /**
     * Get categories assigned to this supplier (many-to-many)
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'supplier_categories', 'supplier_id', 'category_id')
            ->withPivot(['uid', 'is_active', 'priority', 'metadata'])
            ->withTimestamps()
            ->using(SupplierCategory::class);
    }

    /**
     * Get only active categories for this supplier
     */
    public function activeCategories(): BelongsToMany
    {
        return $this->categories()->wherePivot('is_active', true);
    }

    /**
     * Get all supplier-category pivot records
     */
    public function supplierCategories(): HasMany
    {
        return $this->hasMany(SupplierCategory::class, 'supplier_id');
    }

    /**
     * Scope: Filter active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by ERP ID
     */
    public function scopeByErpId($query, int $erpId)
    {
        return $query->where('erp_id', $erpId);
    }

    /**
     * Scope: Filter by code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope: Search by name or code
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
                ->orWhere('code', 'ILIKE', "%{$search}%");
        });
    }

    /**
     * Helper: Check if supplier is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Helper: Get supplier display name
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }

    /**
     * Get sources count attribute for DataTables
     */
    public function getSourcesCountAttribute(): int
    {
        return $this->sources()->count();
    }
}
