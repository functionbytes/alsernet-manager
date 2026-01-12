<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla CONVERSIONMONEDA
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_CONVERSIONMONEDA_FCREACION (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: FCREACION
 *
 * ✅ IDX_CONVERSIONMONEDA_IDMONEDA (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDMONEDA
 *
 * PK_CONVERSIONMONEDA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCONVERSIONMONEDA
 *
 */
class Conversionmoneda extends Model
{
    protected $connection = 'oracle';
    protected $table = 'conversionmoneda';
    protected $primaryKey = 'idconversionmoneda';
    public $timestamps = false;

    protected $fillable = [
        'idmoneda', 'factorconversionaeuros', 'not', 'idusuariocre',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Conversionmoneda
     * ✅ Usa PK_CONVERSIONMONEDA (indexado)
     */
    public function conversionmoneda()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Conversionmoneda::class, 'IDCONVERSIONMONEDA', 'IDCONVERSIONMONEDA');
    }

    /**
     * Relación: Moneda
     * ✅ Usa IDX_CONVERSIONMONEDA_IDMONEDA (indexado)
     */
    public function moneda()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Moneda::class, 'IDMONEDA', 'IDMONEDA');
    }

}
