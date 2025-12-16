<?php

namespace App\Models\Prestashop\Langs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prestashop\Shop\Shop;
use App\Models\Prestashop\Carrier;
use App\Models\Prestashop\Language;

class CarrierLang extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_carrier_lang';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_carrier',
        'id_lang',
        'id_shop',
        'delay',
    ];

    protected $casts = [
        'id_carrier' => 'integer',
        'id_lang' => 'integer',
        'id_shop' => 'integer',
    ];


    protected function setKeysForSaveQuery($query)
    {
        return $query->where('id_carrier', $this->getAttribute('id_carrier'))
                     ->where('id_lang', $this->getAttribute('id_lang'))
                     ->where('id_shop', $this->getAttribute('id_shop'));
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class, 'id_carrier');
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
