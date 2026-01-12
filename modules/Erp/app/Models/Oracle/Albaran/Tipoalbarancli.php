<?php

namespace Modules\Erp\Models\Oracle\Albaran;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla TIPOALBARANCLI
 *
 * ÍNDICES DISPONIBLES:
 * PK_TIPOALBARANCLI (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTIPOALBARANCLI
 *
 */
class Tipoalbarancli extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'tipoalbarancli';
    protected $primaryKey = 'idtipoalbarancli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'estado', 'descripcion', 'idusuariocre', 'idusuariomod',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Tipoalbarancli
     * ✅ Usa PK_TIPOALBARANCLI (indexado)
     */
    public function tipoalbarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\Tipoalbarancli::class, 'IDTIPOALBARANCLI', 'IDTIPOALBARANCLI');
    }

}
