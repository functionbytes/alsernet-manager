<?php

namespace Modules\Erp\Models\Oracle\Promocion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LPROMOCION
 *
 * ÍNDICES DISPONIBLES:
 * PK_LPROMOCION (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPROMOCION
 *
 */
class Lpromocion extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lpromocion';
    protected $primaryKey = 'idlpromocion';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idpromocion', 'importe_desde', 'not', 'importe_hasta', 'not',
        'porc_descuento_promocion', 'importe_descuento_promocion', 'idusuariomod', 'idusuariobaja', 'idusuariocre',
        'nbonos', 'importeminimoventa', 'porc_sobre',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lpromocion
     * ✅ Usa PK_LPROMOCION (indexado)
     */
    public function lpromocion()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Lpromocion::class, 'IDLPROMOCION', 'IDLPROMOCION');
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
