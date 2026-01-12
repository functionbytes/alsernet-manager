<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LCIERRE_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * PK_LCIERRE_CENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLCIERRE
 *
 */
class Lcierre extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lcierre_central';
    protected $primaryKey = 'idlcierre';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idformapago', 'idcierre', 'estado', 'idusuariomod', 'importe',
        'not', 'descuadre', 'not', 'idlcierrec', 'idcierrec',
        'remanente',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lcierre
     * ✅ Usa PK_LCIERRE_CENTRAL (indexado)
     */
    public function lcierre()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Lcierre::class, 'IDLCIERRE', 'IDLCIERRE');
    }

    /**
     * Relación: Formapago
     * ⚠️  SIN ÍNDICE en IDFORMAPAGO
     */
    public function formapago()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\Formapago::class, 'IDFORMAPAGO', 'IDFORMAPAGO');
    }

    /**
     * Relación: Cierre
     * ⚠️  SIN ÍNDICE en IDCIERRE
     */
    public function cierre()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Cierre::class, 'IDCIERRE', 'IDCIERRE');
    }

}
