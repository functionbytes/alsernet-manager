<?php

namespace App\Models\Prestashop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prestashop\Shop\ShopGroup;
use App\Models\Prestashop\Shop\Shop;

class Configuration extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_configuration';
    protected $primaryKey = 'id_configuration';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'id_shop_group',
        'id_shop',
        'value',
        'date_add',
        'date_upd',
    ];

        protected $casts = [
        'date_add' => 'datetime',
        'date_upd' => 'datetime',
        'id_shop_group' => 'integer',
        'id_shop' => 'integer',
    ];

    public function shopGroup(): BelongsTo
    {
        return $this->belongsTo(ShopGroup::class, 'id_shop_group');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'id_shop');
    }
}
