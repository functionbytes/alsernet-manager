<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseShop extends Model
{
    use HasFactory;

    protected $table = 'warehouse_shops';

    protected $fillable = [
        'uid',
        'title',
        'slug',
        'reference',
        'barcode',
        'stock',
        'available',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'available' => 'boolean',
        ];
    }
}
