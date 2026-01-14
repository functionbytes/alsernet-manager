<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla STOCK_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_STOCK_CENTRAL_IDALM (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDALMACEN
 *
 * ✅ IDX_STOCK_CENTRAL_IDART (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTICULO
 *
 * PK_STOCK_CENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDSTOCK
 *
 */
class StockCentral extends Model
{
    protected $connection = 'oracle';
    protected $table = 'stock_central';
    protected $primaryKey = 'idstock';
    public $timestamps = false;

    protected $fillable = [
        'idalmacen', 'idarticulo', 'idteststock', 'estado', 'unidades',
        'not',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];
}
