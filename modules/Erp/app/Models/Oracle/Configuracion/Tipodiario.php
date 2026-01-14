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
class Tipodiario extends Model
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


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Tipodiario
     * ✅ Usa PK_TIPODIARIO_CENT (indexado)
     */
    public function tipodiario()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tipodiario::class, 'IDTIPODIARIO', 'IDTIPODIARIO');
    }

}
