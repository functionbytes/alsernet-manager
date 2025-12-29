<?php

namespace Modules\Supplier\Entities;

use App\Models\Traits\HasUid;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * SupplierCategory Pivot Model
 *
 * Manages many-to-many relationship between Suppliers and Categories
 *
 * @property int $id
 * @property string $uid ULID unique identifier
 * @property int $supplier_id
 * @property int $category_id
 * @property bool $is_active
 * @property int $priority
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Supplier\Supplier $supplier
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supplier\SupplierPrompt> $prompts
 */
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
