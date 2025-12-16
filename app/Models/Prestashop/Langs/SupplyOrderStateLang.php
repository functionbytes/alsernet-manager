<?php

namespace App\Models\Prestashop\Langs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prestashop\Stock\SupplyOrderState;
use App\Models\Prestashop\Shop\Shop;
use App\Models\Prestashop\Language;

class SupplyOrderStateLang extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_supply_order_state_lang';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_supply_order_state',
        'id_lang',
        'id_shop',
        'name',
    ];

    protected $casts = [
        'id_supply_order_state' => 'integer',
        'id_lang' => 'integer',
        'id_shop' => 'integer',
    ];


    protected function setKeysForSaveQuery($query)
    {
        return $query->where('id_supply_order_state', $this->getAttribute('id_supply_order_state'))
                     ->where('id_lang', $this->getAttribute('id_lang'))
                     ->where('id_shop', $this->getAttribute('id_shop'));
    }

    public function supplyOrderState(): BelongsTo
    {
        return $this->belongsTo(SupplyOrderState::class, 'id_supply_order_state');
    }

    public function lang(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'id_lang');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'id_shop');
    }
}
