<?php

namespace App\Models\Prestashop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prestashop\Shop\Shop;
use App\Models\Prestashop\Shop\ShopGroup;

class Connection extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_connections';
    protected $primaryKey = 'id_connections';
    public $timestamps = false;

    protected $fillable = [
        'id_guest',
        'id_page',
        'ip_address',
        'http_referer',
        'id_shop',
        'id_shop_group',
        'date_add',
    ];

        protected $casts = [
        'date_add' => 'datetime',
        'id_guest' => 'integer',
        'id_page' => 'integer',
        'id_shop' => 'integer',
        'id_shop_group' => 'integer',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'id_shop');
    }

    public function shopGroup(): BelongsTo
    {
        return $this->belongsTo(ShopGroup::class, 'id_shop_group');
    }
}
