<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla STOCK_CENTRAL_WEB_HIST
 */
class StockCentralWebHist extends Model
{
    protected $connection = 'oracle';
    protected $table = 'stock_central_web_hist';
    protected $primaryKey = 'idarticulo';
    public $timestamps = false;

    protected $fillable = [
        'unidades', 'idstock_central_web', 'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Articulo
     * ⚠️  SIN ÍNDICE en IDARTICULO
     */
    public function articulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: StockCentralWeb
     * ⚠️  SIN ÍNDICE en IDSTOCK_CENTRAL_WEB
     */
    public function stockCentralWeb()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\StockCentralWeb::class, 'IDSTOCK_CENTRAL_WEB', 'IDSTOCK_CENTRAL_WEB');
    }

}
