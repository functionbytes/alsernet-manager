<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla STOCK_CENTRAL_WEB
 *
 * ÍNDICES DISPONIBLES:
 * PK_STOCK_CENTRAL_WEB (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDSTOCK_CENTRAL_WEB
 *
 * ✅ UK_STOCK_CENTRAL_WEB_IDART (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTICULO
 *
 */
class StockCentralWeb extends Model
{
    protected $connection = 'oracle';
    protected $table = 'stock_central_web';
    protected $primaryKey = 'idstock_central_web';
    public $timestamps = false;

    protected $fillable = [
        'idarticulo', 'unidades', 'not',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Articulo
     * ✅ Usa UK_STOCK_CENTRAL_WEB_IDART (indexado)
     */
    public function articulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: StockCentralWeb
     * ✅ Usa PK_STOCK_CENTRAL_WEB (indexado)
     */
    public function stockCentralWeb()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\StockCentralWeb::class, 'IDSTOCK_CENTRAL_WEB', 'IDSTOCK_CENTRAL_WEB');
    }

}
