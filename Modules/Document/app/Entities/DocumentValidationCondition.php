<?php

namespace Modules\Documents\Entities;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;

class DocumentValidationCondition extends Model
{
    protected $table = 'document_validation_conditions';

    use HasUid;

    // Condition Types
    public const TYPE_SALE_TYPE_MATCH = 'sale_type_match';

    public const TYPE_MODEL_FIELD = 'model_field';

    public const TYPE_CUSTOM_EXPRESSION = 'custom_expression';

    public const AVAILABLE_TYPES = [
        self::TYPE_SALE_TYPE_MATCH => 'Coincidencia de etiquetas',
        self::TYPE_MODEL_FIELD => 'Campo del modelo',
        self::TYPE_CUSTOM_EXPRESSION => 'Expresión personalizada',
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
