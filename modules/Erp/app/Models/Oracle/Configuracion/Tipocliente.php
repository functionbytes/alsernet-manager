<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla TIPOCLIENTE
 *
 * ÍNDICES DISPONIBLES:
 * PK_TIPOCLIENTE (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTIPOCLIENTE
 *
 */
class Tipocliente extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'tipocliente';
    protected $primaryKey = 'idtipocliente';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'estado', 'descripcion', 'idusuariomod',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Tipocliente
     * ✅ Usa PK_TIPOCLIENTE (indexado)
     */
    public function tipocliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tipocliente::class, 'IDTIPOCLIENTE', 'IDTIPOCLIENTE');
    }

}
