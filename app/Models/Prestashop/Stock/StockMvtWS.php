<?php

namespace App\Models\Prestashop\Stock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prestashop\Stock\StockMvtReason;
use App\Models\Prestashop\Stock\Warehouse;
use App\Models\Prestashop\Employee;
use App\Models\Prestashop\Product;
use App\Models\Prestashop\Combination;
use App\Models\Prestashop\Currency;

class StockMvtWS extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_stock_mvt';
    protected $primaryKey = 'id_stock_mvt';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'date_add',
        'id_employee',
        'employee_firstname',
        'employee_lastname',
        'id_stock',
        'physical_quantity',
        'id_stock_mvt_reason',
        'sign',
        'price_te',
        'referer',
        'id_product',
        'id_product_attribute',
        'id_warehouse',
        'id_currency',
        'management_type',
        'product_name',
        'upc',
        'mpn',
        'reference',
    ];

        protected $casts = [
        'date_add' => 'datetime',
        'id_employee' => 'integer',
        'id_stock' => 'integer',
        'id_stock_mvt_reason' => 'integer',
        'id_product' => 'integer',
        'id_product_attribute' => 'integer',
        'id_warehouse' => 'integer',
        'id_currency' => 'integer',
        'price_te' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'id_employee');
    }

    public function stockMvtReason(): BelongsTo
    {
        return $this->belongsTo(StockMvtReason::class, 'id_stock_mvt_reason');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function productAttribute(): BelongsTo
    {
        return $this->belongsTo(Combination::class, 'id_product_attribute');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'id_warehouse');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'id_currency');
    }
}
