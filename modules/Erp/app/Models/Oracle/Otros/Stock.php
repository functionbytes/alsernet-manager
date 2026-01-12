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
class Stock extends Model
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


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Stock
     * ✅ Usa PK_STOCK_CENTRAL (indexado)
     */
    public function stock()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Stock::class, 'IDSTOCK', 'IDSTOCK');
    }

    /**
     * Relación: Almacen
     * ✅ Usa IDX_STOCK_CENTRAL_IDALM (indexado)
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

    /**
     * Relación: Articulo
     * ✅ Usa IDX_STOCK_CENTRAL_IDART (indexado)
     */
    public function articulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: Teststock
     * ⚠️  SIN ÍNDICE en IDTESTSTOCK
     */
    public function teststock()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Teststock::class, 'IDTESTSTOCK', 'IDTESTSTOCK');
    }

}
