<?php

namespace App\Models\Document;

use App\Models\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;

/**
 * DocumentValidationCondition
 *
 * Centralized configuration for validation conditions.
 * Supports three types of conditions:
 * - SALE_TYPE_MATCH: Maps to sale_types from document_product_blockades
 * - MODEL_FIELD: Validates against model field values
 * - CUSTOM_EXPRESSION: Custom validation logic/expressions
 *
 * Example:
 * - type: sale_type_match, key: 'is_weapon', sale_types: ['escopeta', 'rifle', 'corta']
 * - type: model_field, key: 'is_national', model_field: 'customer.country_id', expected_value: [1]
 * - type: custom_expression, key: 'is_priority', validation_expression: 'order_total > 1000'
 */
class DocumentValidationCondition extends Model
{
    use HasUid;

    // Condition Types
    public const TYPE_SALE_TYPE_MATCH = 'sale_type_match';

    public const TYPE_MODEL_FIELD = 'model_field';

    public const TYPE_CUSTOM_EXPRESSION = 'custom_expression';

    public const AVAILABLE_TYPES = [
        self::TYPE_SALE_TYPE_MATCH => 'Coincidencia de Sale Type',
        self::TYPE_MODEL_FIELD => 'Campo del Modelo',
        self::TYPE_CUSTOM_EXPRESSION => 'Expresión Personalizada',
    ];

    protected $fillable = [
        'uid',
        'key',
        'condition_type',
        'name',
        'description',
        'sale_types',
        'model_field',
        'expected_value',
        'validation_expression',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sale_types' => 'array',
            'expected_value' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Check if a given sale_type matches this condition
     *
     * @param  string  $saleType  The sale_type to check (from getSaleType())
     */
    public function matches(string $saleType): bool
    {
        if (! $this->is_active || empty($this->sale_types)) {
            return false;
        }

        return in_array($saleType, $this->sale_types);
    }

    /**
     * Get condition by key (cached)
     */
    public static function getByKey(string $key): ?self
    {
        return cache()->remember("validation_condition:{$key}", 3600, function () use ($key) {
            return self::active()->byKey($key)->first();
        });
    }

    /**
     * Clear condition cache
     */
    public static function clearCache(?string $key = null): void
    {
        if ($key) {
            cache()->forget("validation_condition:{$key}");
        } else {
            // Clear all condition caches
            $keys = self::pluck('key');
            foreach ($keys as $conditionKey) {
                cache()->forget("validation_condition:{$conditionKey}");
            }
        }
    }

    /**
     * Boot method to clear cache on save/delete
     */
    protected static function booted(): void
    {
        static::saved(function ($condition) {
            self::clearCache($condition->key);
        });

        static::deleted(function ($condition) {
            self::clearCache($condition->key);
        });
    }
}
