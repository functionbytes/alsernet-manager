<?php

namespace Modules\Erp\Models\Oracle\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla W_CARACTERISTICAS_PROD_IDIOMA
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_WCARACT_PROIDI_WCARACPROD (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID_CARACTERISTICA
 *
 * PK_W_CARACTERISTICAS_PROD_IDIO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID
 *
 */
class WCaracteristicasProdIdioma extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'w_caracteristicas_prod_idioma';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'nombre', 'idioma', 'id_caracteristica', 'estado', 'idusuariocre',
        'idusuariomod', 'idusuariobaja',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con WCaracteristicasProd
     */
    public function _caracteristica()
    {
        return $this->belongsTo(WCaracteristicasProd::class, 'id_caracteristica', 'idw_caracteristicas_prod');
    }


    /**
     * Relación: Caracteristica
     * ✅ Usa IDX_WCARACT_PROIDI_WCARACPROD (indexado)
     */
    public function caracteristica()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WCaracteristicasProd::class, 'ID_CARACTERISTICA', 'ID');
    }


    /**
     * Relación: WAyudas
     * ✅ Usa PK_W_CARACTERISTICAS_PROD_IDIO (indexado)
     */
    public function wAyudas()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WAyudas::class, 'ID', 'ID');
    }

}
