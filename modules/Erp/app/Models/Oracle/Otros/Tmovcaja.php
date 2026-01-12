<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla TMOVCAJA
 *
 * ÍNDICES DISPONIBLES:
 * PK_TMOVCAJA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTMOVCAJA
 *
 */
class Tmovcaja extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'tmovcaja';
    protected $primaryKey = 'idtmovcaja';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'estado', 'idusuariomod', 'oporigen', 'opdestino', 'descripcion',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Tmovcaja
     * ✅ Usa PK_TMOVCAJA (indexado)
     */
    public function tmovcaja()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Tmovcaja::class, 'IDTMOVCAJA', 'IDTMOVCAJA');
    }

}
