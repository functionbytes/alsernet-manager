<?php

namespace App\Models\Prestashop\Langs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prestashop\Shop\Shop;
use App\Models\Prestashop\Language;

class MetaLang extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_meta_lang';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_meta',
        'id_lang',
        'id_shop',
        'title',
        'description',
        'keywords',
        'url_rewrite',
    ];

    protected $casts = [
        'id_meta' => 'integer',
        'id_lang' => 'integer',
        'id_shop' => 'integer',
    ];


    protected function setKeysForSaveQuery($query)
    {
        return $query->where('id_meta', $this->getAttribute('id_meta'))
                     ->where('id_lang', $this->getAttribute('id_lang'))
                     ->where('id_shop', $this->getAttribute('id_shop'));
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
