<?php

namespace Modules\Erp\Models\Oracle\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla W_VALORES_NAV_IDIOMA
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_WVALNAVIDIOMA_WVALORESNAV (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID_VALOR
 *
 * PK_W_VALORES_NAV_IDIOMA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID
 *
 */
class WValoresNavIdioma extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'w_valores_nav_idioma';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'id_valor', 'nombre', 'idioma', 'estado', 'idusuariocre',
        'idusuariomod', 'idusuariobaja', 'seo_title', 'seo_metadescriptions', 'seo_texto_superior',
        'seo_texto_inferior',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con WValoresNav
     */
    public function _valor()
    {
        return $this->belongsTo(WValoresNav::class, 'id_valor', 'idw_valores_nav');
    }


    /**
     * Relación: Valor
     * ✅ Usa IDX_WVALNAVIDIOMA_WVALORESNAV (indexado)
     */
    public function valor()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WValoresNav::class, 'ID_VALOR', 'ID');
    }


    /**
     * Relación: WAyudas
     * ✅ Usa PK_W_VALORES_NAV_IDIOMA (indexado)
     */
    public function wAyudas()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WAyudas::class, 'ID', 'ID');
    }

}
