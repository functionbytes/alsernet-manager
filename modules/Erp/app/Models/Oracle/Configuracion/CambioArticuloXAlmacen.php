<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla CAMBIO_ARTICULO_X_ALMACEN
 *
 * ÍNDICES DISPONIBLES:
 * PK_CAMBIO_ARTICULO_X_ALMACEN (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCAMBIO_ARTICULO_X_ALMACEN
 *
 */
class CambioArticuloXAlmacen extends Model
{
    protected $connection = 'oracle';
    protected $table = 'cambio_articulo_x_almacen';
    protected $primaryKey = 'idcambio_articulo_x_almacen';
    public $timestamps = false;

    protected $fillable = [
        'idcambio_articulo', 'idalmacen', 'procesado', 'fprocesado',
    ];

    protected $casts = [
        'fprocesado' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: CambioArticuloXAlmacen
     * ✅ Usa PK_CAMBIO_ARTICULO_X_ALMACEN (indexado)
     */
    public function cambioArticuloXAlmacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\CambioArticuloXAlmacen::class, 'IDCAMBIO_ARTICULO_X_ALMACEN', 'IDCAMBIO_ARTICULO_X_ALMACEN');
    }

    /**
     * Relación: CambioArticulo
     * ⚠️  SIN ÍNDICE en IDCAMBIO_ARTICULO
     */
    public function cambioArticulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\CambioArticulo::class, 'IDCAMBIO_ARTICULO', 'IDCAMBIO_ARTICULO');
    }

    /**
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

}
