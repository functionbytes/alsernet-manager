<?php

namespace Modules\Erp\Models\Oracle\Articulo;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla ARTICULOSTOCKMINMAX
 *
 * ÍNDICES DISPONIBLES:
 * PK_ARTICULOSTOCKMINMAX (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTICULOSTOCKMINMAX
 *
 */
class Articulostockminmax extends Model
{
    protected $connection = 'oracle';
    protected $table = 'articulostockminmax';
    protected $primaryKey = 'idarticulostockminmax';
    public $timestamps = false;

    protected $fillable = [
        'idarticulo', 'stockmintotal', 'stockmaxtotal', 'stockrecomendado', 'idalmacen',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Articulostockminmax
     * ✅ Usa PK_ARTICULOSTOCKMINMAX (indexado)
     */
    public function articulostockminmax()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Articulo\Articulostockminmax::class, 'IDARTICULOSTOCKMINMAX', 'IDARTICULOSTOCKMINMAX');
    }

    /**
     * Relación: Articulo
     * ⚠️  SIN ÍNDICE en IDARTICULO
     */
    public function articulo()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

}
