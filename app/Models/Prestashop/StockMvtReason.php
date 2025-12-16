<?php

namespace App\Models\Prestashop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prestashop\Stock\StockMvtReason;

class StockMvtReason extends Model
{
    protected $connection = 'prestashop';
    protected $table = 'aalv_stock_mvt_reason';
    protected $primaryKey = 'id_stock_mvt_reason';
    public $timestamps = false;

    protected $fillable = [
        'id_stock_mvt_reason',
        'name',
        'sign',
        'date_add',
        'date_upd',
        'deleted',
    ];

        protected $casts = [
        'date_add' => 'datetime',
        'date_upd' => 'datetime',
        'deleted' => 'boolean',
        'id_stock_mvt_reason' => 'integer',
    ];

    public function stockMvtReason(): BelongsTo
    {
        return $this->belongsTo(StockMvtReason::class, 'id_stock_mvt_reason');
    }
}
