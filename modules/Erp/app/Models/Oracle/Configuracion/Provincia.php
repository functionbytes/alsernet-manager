<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla PROVINCIA
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_PROVINCIA_IDREGFISCAL (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDREGFISCAL
 *
 * PK_PROVINCIA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPROVINCIA
 *
 * ⚠️  UK_PROVINCIA_DESCRIPCION_PAIS (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: DESCRIPCION, IDPAIS
 *
 */
class Provincia extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'provincia';
    protected $primaryKey = 'idprovincia';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'descripcion', 'idpais', 'estado', 'idregfiscal', 'idusuariomod',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con Regfiscal
     */
    public function regfiscal()
    {
        return $this->belongsTo(Regfiscal::class, 'idregfiscal', 'idregfiscal');
    }


    /**
     * Relación: Provincia
     * ✅ Usa PK_PROVINCIA (indexado)
     */
    public function provincia()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Provincia::class, 'IDPROVINCIA', 'IDPROVINCIA');
    }

    /**
     * Relación: Pais
     * ⚠️  SIN ÍNDICE en IDPAIS
     */
    public function pais()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Pais::class, 'IDPAIS', 'IDPAIS');
    }

}
