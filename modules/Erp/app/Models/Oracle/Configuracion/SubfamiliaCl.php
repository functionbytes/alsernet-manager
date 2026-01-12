<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Promocion\Lpromocionsubfamiliaincluida;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla SUBFAMILIA_CL
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_SUBFAMILIA_FAMILIA (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDFAMILIA_CL
 *
 * PK_SUBFAMILIA_CL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDSUBFAMILIA_CL
 *
 */
class SubfamiliaCl extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'subfamilia_cl';
    protected $primaryKey = 'idsubfamilia_cl';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idfamilia_cl', 'estado', 'idusuariocre', 'idusuariomod', 'idusuariobaj',
        'descripcion', 'desc_corta', 'escartucheria', 'esmunicionmetalica', 'mostrarlotes',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación inversa con Lpromocionsubfamiliaincluida
     */
    public function lpromocionsubfamiliaincluidas()
    {
        return $this->hasMany(Lpromocionsubfamiliaincluida::class, 'idsubfamilia_cl', 'idsubfamilia_cl');
    }


    /**
     * Relación: SubfamiliaCl
     * ✅ Usa PK_SUBFAMILIA_CL (indexado)
     */
    public function subfamiliaCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\SubfamiliaCl::class, 'IDSUBFAMILIA_CL', 'IDSUBFAMILIA_CL');
    }

    /**
     * Relación: FamiliaCl
     * ✅ Usa IDX_SUBFAMILIA_FAMILIA (indexado)
     */
    public function familiaCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\FamiliaCl::class, 'IDFAMILIA_CL', 'IDFAMILIA_CL');
    }

}
