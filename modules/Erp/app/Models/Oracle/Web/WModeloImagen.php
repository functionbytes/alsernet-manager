<?php

namespace Modules\Erp\Models\Oracle\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla W_MODELO_IMAGEN
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_W_MODELO_IMAGEN_IDMOD (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID_MODELO
 *
 * PK_W_MODELO_IMAGEN (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID
 *
 */
class WModeloImagen extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'w_modelo_imagen';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'id_modelo', 'path_imagen', 'orden', 'estado', 'idusuariocre',
        'idusuariomod', 'idusuariobaja', 'idmodelo',
    ];

    protected $casts = [
        'estado' => 'boolean',
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
     * ✅ Usa IDX_W_MODELO_IMAGEN_IDMOD (indexado)
     */
    public function modelo()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WModelo::class, 'ID_MODELO', 'ID');
    }


    /**
     * Relación: WAyudas
     * ✅ Usa PK_W_MODELO_IMAGEN (indexado)
     */
    public function wAyudas()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WAyudas::class, 'ID', 'ID');
    }

}
