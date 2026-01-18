<?php

namespace Modules\Supplier\Models;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\Catalog\Models\Category;

class SupplierCategory extends Pivot
{
    use HasFactory, HasUid;

    protected $table = 'supplier_categories';

    public $incrementing = true;

    protected $fillable = [
        'supplier_id',
        'category_id',
        'is_active',
        'priority',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the supplier for this assignment
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get the category for this assignment
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get prompts specific to this supplier-category combination
     */
    public function prompts(): HasMany
    {
        return $this->hasMany(SupplierPrompt::class, 'supplier_id', 'supplier_id')
            ->where('category_id', $this->category_id);
    }

    /**
     * Scope: Filter only active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by supplier
     */
    public function scopeForSupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope: Filter by category
     */
    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Order by priority (descending)
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}
