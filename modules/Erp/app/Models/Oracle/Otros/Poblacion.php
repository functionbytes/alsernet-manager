<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla POBLACION
 *
 * ÍNDICES DISPONIBLES:
 * PK_POBLACION (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPOBLACION
 *
 */
class Poblacion extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'poblacion';
    protected $primaryKey = 'idpoblacion';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'descripcion', 'idprovincia', 'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Poblacion
     * ✅ Usa PK_POBLACION (indexado)
     */
    public function poblacion()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Poblacion::class, 'IDPOBLACION', 'IDPOBLACION');
    }

    /**
     * Relación: Provincia
     * ⚠️  SIN ÍNDICE en IDPROVINCIA
     */
    public function provincia()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Provincia::class, 'IDPROVINCIA', 'IDPROVINCIA');
    }

}
