<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $document_id
 * @property int|null $product_id Prestashop product ID
 * @property string $product_name Product name at time of document creation
 * @property string|null $product_reference Product reference code
 * @property int $quantity Quantity ordered
 * @property numeric|null $price Unit price at time of document creation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Document\Document $document
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct whereProductReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentProduct whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class DocumentProduct extends Model
{
    protected $table = 'document_products';

    protected $fillable = [
        'document_id',
        'product_id',
        'product_name',
        'product_reference',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
