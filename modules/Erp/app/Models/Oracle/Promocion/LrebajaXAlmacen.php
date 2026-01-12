<?php

namespace Modules\Erp\Models\Oracle\Promocion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LREBAJA_X_ALMACEN
 *
 * ÍNDICES DISPONIBLES:
 * PK_LREBAJA_X_ALM (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLREBAJA_X_ALMACEN
 *
 */
class LrebajaXAlmacen extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lrebaja_x_almacen';
    protected $primaryKey = 'idlrebaja_x_almacen';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idrebaja', 'idalmacen', 'estado', 'idusuariocre', 'idusuariomod',
        'idusuariobaj',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: LrebajaXAlmacen
     * ✅ Usa PK_LREBAJA_X_ALM (indexado)
     */
    public function lrebajaXAlmacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\LrebajaXAlmacen::class, 'IDLREBAJA_X_ALMACEN', 'IDLREBAJA_X_ALMACEN');
    }

    /**
     * Relación: Rebaja
     * ⚠️  SIN ÍNDICE en IDREBAJA
     */
    public function rebaja()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Rebaja::class, 'IDREBAJA', 'IDREBAJA');
    }

    /**
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

}
