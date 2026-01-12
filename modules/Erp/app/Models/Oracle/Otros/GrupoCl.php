<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla GRUPO_CL
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_GRUPO_SUBFAMILIA (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDSUBFAMILIA_CL
 *
 * PK_GRUPO_CL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDGRUPO_CL
 *
 */
class GrupoCl extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'grupo_cl';
    protected $primaryKey = 'idgrupo_cl';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idsubfamilia_cl', 'estado', 'idusuariocre', 'idusuariomod', 'idusuariobaj',
        'descripcion', 'desc_corta', 'prefijo', 'prox_num', 'excluir_pedir_a_tienda',
        'pedir_numero_serie', 'intrastat',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: GrupoCl
     * ✅ Usa PK_GRUPO_CL (indexado)
     */
    public function grupoCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\GrupoCl::class, 'IDGRUPO_CL', 'IDGRUPO_CL');
    }

    /**
     * Relación: SubfamiliaCl
     * ✅ Usa IDX_GRUPO_SUBFAMILIA (indexado)
     */
    public function subfamiliaCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\SubfamiliaCl::class, 'IDSUBFAMILIA_CL', 'IDSUBFAMILIA_CL');
    }

}
