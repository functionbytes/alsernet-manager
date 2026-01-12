<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla TARJETAS
 *
 * ÍNDICES DISPONIBLES:
 * PK_TARJETAS (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTARJETA
 *
 */
class Tarjetas extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'tarjetas';
    protected $primaryKey = 'idtarjeta';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'descripcion', 'alias', 'idusuariocre', 'idusuariomod', 'idusuariobaja',
        'estado', 'estarjetacredito',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Tarjeta
     * ✅ Usa PK_TARJETAS (indexado)
     */
    public function tarjeta()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Tarjetas::class, 'IDTARJETA', 'IDTARJETA');
    }

}
