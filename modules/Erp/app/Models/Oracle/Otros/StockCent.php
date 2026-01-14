<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla STOCK_CENT_TPVCOR
 *
 * ÍNDICES DISPONIBLES:
 * PK_STOCK_CENT_TPVCOR (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDSTOCK
 *
 */
class StockCent extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'stock_cent_tpvcor';
    protected $primaryKey = 'idstock';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idalmacen', 'idarticulo', 'idteststock', 'estado', 'unidades',
        'not', 'idalmacen_creacion',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Stock
     * ✅ Usa PK_STOCK_CENT_TPVCOR (indexado)
     */
    public function stock()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Stock::class, 'IDSTOCK', 'IDSTOCK');
    }

    /**
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

    /**
     * Relación: Articulo
     * ⚠️  SIN ÍNDICE en IDARTICULO
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
