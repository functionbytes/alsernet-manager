<?php

namespace Modules\Erp\Models\Oracle\Promocion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LPROMOCIONTAGEXCLUIDO
 *
 * ÍNDICES DISPONIBLES:
 * PK_LPROMOCIONTAGEXCLUIDO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPROMOCIONTAGEXCLUIDO
 *
 */
class Lpromociontagexcluido extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lpromociontagexcluido';
    protected $primaryKey = 'idlpromociontagexcluido';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idpromocion', 'tagarticulo', 'estado', 'idusuariocre', 'idusuariomod',
        'idusuariobaj',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lpromociontagexcluido
     * ✅ Usa PK_LPROMOCIONTAGEXCLUIDO (indexado)
     */
    public function lpromociontagexcluido()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Lpromociontagexcluido::class, 'IDLPROMOCIONTAGEXCLUIDO', 'IDLPROMOCIONTAGEXCLUIDO');
    }

    /**
     * Relación: Promocion
     * ⚠️  SIN ÍNDICE en IDPROMOCION
     */
    public function promocion()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Promocion::class, 'IDPROMOCION', 'IDPROMOCION');
    }

}
