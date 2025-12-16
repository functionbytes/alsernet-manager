<?php

namespace App\Models\Prestashop;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_feature';
    protected $primaryKey = 'id_feature';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'position',
    ];

        protected $casts = [
        'position' => 'integer',
    ];
}
