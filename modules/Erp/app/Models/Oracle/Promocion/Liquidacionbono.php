<?php

namespace Modules\Erp\Models\Oracle\Promocion;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LIQUIDACIONBONO
 *
 * ÍNDICES DISPONIBLES:
 * PK_IDLIQUIDACIONBONO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLIQUIDACIONBONO
 *
 */
class Liquidacionbono extends Model
{
    protected $connection = 'oracle';
    protected $table = 'liquidacionbono';
    protected $primaryKey = 'idliquidacionbono';
    public $timestamps = false;

    protected $fillable = [
        'fliquidacion',
    ];

    protected $casts = [
        'fliquidacion' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Liquidacionbono
     * ✅ Usa PK_IDLIQUIDACIONBONO (indexado)
     */
    public function liquidacionbono()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Liquidacionbono::class, 'IDLIQUIDACIONBONO', 'IDLIQUIDACIONBONO');
    }

}
