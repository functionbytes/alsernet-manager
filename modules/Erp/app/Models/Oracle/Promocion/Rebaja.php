<?php

namespace Modules\Erp\Models\Oracle\Promocion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla REBAJA
 *
 * ÍNDICES DISPONIBLES:
 * PK_REBAJA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDREBAJA
 *
 */
class Rebaja extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'rebaja';
    protected $primaryKey = 'idrebaja';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'finicio', 'ffin', 'estado', 'idusuariocre', 'idususariomod',
        'idusuariobaj', 'nombre',
    ];

    protected $casts = [
        'finicio' => 'datetime',
        'ffin' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Rebaja
     * ✅ Usa PK_REBAJA (indexado)
     */
    public function rebaja()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Rebaja::class, 'IDREBAJA', 'IDREBAJA');
    }

}
