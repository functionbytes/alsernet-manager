<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla TIPODIRECCION
 *
 * ÍNDICES DISPONIBLES:
 * PK_TIPODIRECCION (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTIPODIRECCION
 *
 */
class Tipodireccion extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'tipodireccion';
    protected $primaryKey = 'idtipodireccion';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'descripcion', 'idusuariocre', 'idusuariomod', 'idusuariobaja', 'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Tipodireccion
     * ✅ Usa PK_TIPODIRECCION (indexado)
     */
    public function tipodireccion()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tipodireccion::class, 'IDTIPODIRECCION', 'IDTIPODIRECCION');
    }

}
