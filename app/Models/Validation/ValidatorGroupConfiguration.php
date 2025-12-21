<?php

namespace App\Models\Validation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidatorGroupConfiguration extends Model
{
    protected $fillable = [
        'validator_group_id',
        'key',
        'label',
        'description',
        'value',
        'category',
        'order',
        'is_active',
    ];

    protected $casts = [
        'value' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relación con ValidatorGroup
     */
    public function validatorGroup(): BelongsTo
    {
        return $this->belongsTo(ValidatorGroup::class);
    }

    /**
     * Scope para obtener configuraciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para obtener por categoría
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Obtener todas las configuraciones de un grupo
     */
    public static function getGroupConfigurations(int $groupId, ?string $category = null)
    {
        $query = static::where('validator_group_id', $groupId)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('order');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->get();
    }

    /**
     * Verificar si una configuración está habilitada
     */
    public static function isEnabled(int $groupId, string $key): bool
    {
        return static::where('validator_group_id', $groupId)
            ->where('key', $key)
            ->where('is_active', true)
            ->value('value') ?? false;
    }
}
