<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla PRIORIDAD
 *
 * ÍNDICES DISPONIBLES:
 * PK_PRIORIDAD (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPRIORIDAD
 *
 */
class Prioridad extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'prioridad';
    protected $primaryKey = 'idprioridad';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'descripcion', 'idusuariocre', 'idusuariomod', 'estado', 'nivel',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Prioridad
     * ✅ Usa PK_PRIORIDAD (indexado)
     */
    public function prioridad()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Prioridad::class, 'IDPRIORIDAD', 'IDPRIORIDAD');
    }

}
