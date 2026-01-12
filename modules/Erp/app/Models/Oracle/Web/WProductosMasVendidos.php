<?php

namespace Modules\Erp\Models\Oracle\Web;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla W_PRODUCTOS_MAS_VENDIDOS
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_W_PRODUCTOS_MAS_VENDIDOS_I (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID_MODELO
 *
 * PK_W_PRODUCTOS_MAS_VENDIDOS (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID
 *
 */
class WProductosMasVendidos extends Model
{
    protected $connection = 'oracle';
    protected $table = 'w_productos_mas_vendidos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_tienda', 'id_modelo', 'idmodelo',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con WModelo
     */
    public function _modelo()
    {
        return $this->belongsTo(WModelo::class, 'id_modelo', 'idw_modelo');
    }


    /**
     * Relación: Modelo
     * ✅ Usa IDX_W_PRODUCTOS_MAS_VENDIDOS_I (indexado)
     */
    public function modelo()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WModelo::class, 'ID_MODELO', 'ID');
    }


    /**
     * Relación: WAyudas
     * ✅ Usa PK_W_PRODUCTOS_MAS_VENDIDOS (indexado)
     */
    public function wAyudas()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WAyudas::class, 'ID', 'ID');
    }

}
