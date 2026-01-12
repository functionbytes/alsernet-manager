<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla TIPODIARIO_CENT
 *
 * ÍNDICES DISPONIBLES:
 * PK_TIPODIARIO_CENT (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTIPODIARIO
 *
 */
class TipodiarioCent extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'tipodiario_cent';
    protected $primaryKey = 'idtipodiario';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'descripcion', 'estado', 'idusuariocre', 'idusuariomod', 'idusuariobaj',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];
}
