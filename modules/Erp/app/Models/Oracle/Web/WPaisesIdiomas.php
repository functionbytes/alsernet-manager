<?php

namespace Modules\Erp\Models\Oracle\Web;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla W_PAISES_IDIOMAS
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_WPAISES_IDIOMAS_WPAISES (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID_PAIS
 *
 * PK_W_PAISES_IDIOMAS (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID
 *
 */
class WPaisesIdiomas extends Model
{
    protected $connection = 'oracle';
    protected $table = 'w_paises_idiomas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_pais', 'nombre', 'idioma',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con WPaises
     */
    public function _pais()
    {
        return $this->belongsTo(WPaises::class, 'id_pais', 'idw_paises');
    }


    /**
     * Relación: Pais
     * ✅ Usa IDX_WPAISES_IDIOMAS_WPAISES (indexado)
     */
    public function pais()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WPaises::class, 'ID_PAIS', 'ID');
    }


    /**
     * Relación: WAyudas
     * ✅ Usa PK_W_PAISES_IDIOMAS (indexado)
     */
    public function wAyudas()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WAyudas::class, 'ID', 'ID');
    }

}
