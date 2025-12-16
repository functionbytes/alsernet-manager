<?php

namespace App\Models\Prestashop\Stock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prestashop\Shop\Shop;
use App\Models\Prestashop\Shop\ShopGroup;
use App\Models\Prestashop\Product;
use App\Models\Prestashop\Combination;

class StockAvailable extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_stock_available';
    protected $primaryKey = 'id_stock_available';
    public $timestamps = false;

    protected $fillable = [
        'id_product',
        'id_product_attribute',
        'id_shop',
        'id_shop_group',
    ];

        protected $casts = [
        'id_product' => 'integer',
        'id_product_attribute' => 'integer',
        'id_shop' => 'integer',
        'id_shop_group' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function productAttribute(): BelongsTo
    {
        return $this->belongsTo(Combination::class, 'id_product_attribute');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'id_shop');
    }

    public function shopGroup(): BelongsTo
    {
        return $this->belongsTo(ShopGroup::class, 'id_shop_group');
    }
}
