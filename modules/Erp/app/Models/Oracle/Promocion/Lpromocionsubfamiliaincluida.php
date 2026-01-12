<?php

namespace Modules\Erp\Models\Oracle\Promocion;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Configuracion\SubfamiliaCl;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LPROMOCIONSUBFAMILIAINCLUIDA
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_LPROMOCIONSUBFAMILIAINCLUI (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDSUBFAMILIA_CL
 *
 * PK_LPROMOCIONSUBFAMILIAINCL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPROMOCIONSUBFAMILIAINCLUIDA
 *
 * ⚠️  UK_LPROMSUBFINC_SUBFAM (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPROMOCION, IDSUBFAMILIA_CL
 *
 */
class Lpromocionsubfamiliaincluida extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lpromocionsubfamiliaincluida';
    protected $primaryKey = 'idlpromocionsubfamiliaincluida';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idpromocion', 'idsubfamilia_cl', 'estado', 'idusuariocre', 'idusuariomod',
        'idusuariobaj',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con Promocion
     */
    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'idpromocion', 'idpromocion');
    }

    /**
     * Relación con SubfamiliaCl
     */
    public function subfamilia_cl()
    {
        return $this->belongsTo(SubfamiliaCl::class, 'idsubfamilia_cl', 'idsubfamilia_cl');
    }


    /**
     * Relación: SubfamiliaCl
     * ✅ Usa IDX_LPROMOCIONSUBFAMILIAINCLUI (indexado)
     */
    public function subfamiliaCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\SubfamiliaCl::class, 'IDSUBFAMILIA_CL', 'IDSUBFAMILIA_CL');
    }


    /**
     * Relación: Lpromocionsubfamiliaincluida
     * ✅ Usa PK_LPROMOCIONSUBFAMILIAINCL (indexado)
     */
    public function lpromocionsubfamiliaincluida()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Lpromocionsubfamiliaincluida::class, 'IDLPROMOCIONSUBFAMILIAINCLUIDA', 'IDLPROMOCIONSUBFAMILIAINCLUIDA');
    }

}
