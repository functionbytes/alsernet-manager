<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;

class DocumentProductBlockade extends Model
{
    // Blockade type constants
    public const TYPE_DNI = 'dni';

    public const TYPE_ESCOPETA = 'escopeta';

    public const TYPE_RIFLE = 'rifle';

    public const TYPE_CORTA = 'corta';

    /**
     * All valid blockade types
     */
    public const VALID_TYPES = [
        self::TYPE_DNI,
        self::TYPE_ESCOPETA,
        self::TYPE_RIFLE,
        self::TYPE_CORTA,
    ];

    /**
     * The table associated with the model.
     */
    protected $table = 'document_product_blockades';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'source_id',
        'product_id',
        'product_attribute_id',
        'blockade_type',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'source_id' => 'integer',
            'product_id' => 'integer',
            'product_attribute_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Check if product has a specific blockade type
     * Works with both simple products (product_id) and combinations (product_attribute_id)
     */
    public static function hasBlockade(?int $idProduct = null, ?int $idProductAttribute = null, ?string $blockadeType = null): bool
    {
        $query = static::query();

        if ($idProduct !== null) {
            $query->where('product_id', $idProduct);
        }

        if ($idProductAttribute !== null) {
            $query->where('product_attribute_id', $idProductAttribute);
        }

        if ($blockadeType !== null) {
            $query->where('blockade_type', $blockadeType);
        }

        return $query->exists();
    }

    /**
     * Get all blockade types for a product (simple or combination)
     */
    public static function getBlockadeTypes(?int $idProduct = null, ?int $idProductAttribute = null): array
    {
        $query = static::query();

        if ($idProduct !== null) {
            $query->where('product_id', $idProduct);
        }

        if ($idProductAttribute !== null) {
            $query->where('product_attribute_id', $idProductAttribute);
        }

        return $query->pluck('blockade_type')->toArray();
    }

    /**
     * Check if product has DNI requirement
     */
    public static function requiresDni(?int $idProduct = null, ?int $idProductAttribute = null): bool
    {
        return static::hasBlockade($idProduct, $idProductAttribute, self::TYPE_DNI);
    }

    /**
     * Get sale type for a product (returns first found blockade type)
     */
    public static function getSaleType(?int $idProduct = null, ?int $idProductAttribute = null): ?string
    {
        $query = static::query();

        if ($idProduct !== null) {
            $query->where('product_id', $idProduct);
        }

        if ($idProductAttribute !== null) {
            $query->where('product_attribute_id', $idProductAttribute);
        }

        $blockade = $query->whereIn('blockade_type', [self::TYPE_DNI, self::TYPE_ESCOPETA, self::TYPE_RIFLE, self::TYPE_CORTA])
            ->first();

        return $blockade?->blockade_type;
    }
}
