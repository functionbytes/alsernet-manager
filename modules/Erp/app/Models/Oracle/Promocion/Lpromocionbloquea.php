<?php

namespace Modules\Erp\Models\Oracle\Promocion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LPROMOCIONBLOQUEA
 *
 * ÍNDICES DISPONIBLES:
 * PK_LPROMOCIONBLOQUEA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPROMOCIONBLOQUEA
 *
 */
class Lpromocionbloquea extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lpromocionbloquea';
    protected $primaryKey = 'idlpromocionbloquea';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idpromocion', 'idarticulo', 'estado', 'idusuariocre', 'idusuariomod',
        'idusuariobaj',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lpromocionbloquea
     * ✅ Usa PK_LPROMOCIONBLOQUEA (indexado)
     */
    public function lpromocionbloquea()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Lpromocionbloquea::class, 'IDLPROMOCIONBLOQUEA', 'IDLPROMOCIONBLOQUEA');
    }

    /**
     * Relación: Promocion
     * ⚠️  SIN ÍNDICE en IDPROMOCION
     */
    public function promocion()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Promocion::class, 'IDPROMOCION', 'IDPROMOCION');
    }

    /**
     * Relación: Articulo
     * ⚠️  SIN ÍNDICE en IDARTICULO
     */
    public function articulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

}
