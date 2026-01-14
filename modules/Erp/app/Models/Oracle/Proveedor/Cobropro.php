<?php

namespace Modules\Erp\Models\Oracle\Proveedor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla COBROPRO
 *
 * ÍNDICES DISPONIBLES:
 * PK_IDCOBROPRO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCOBROPRO
 *
 */
class Cobropro extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'cobropro';
    protected $primaryKey = 'idcobropro';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idusuariocre', 'idusuariomod', 'idusuariobaj', 'borrado', 'idformapago',
        'idcaja', 'idmovcaja', 'idtcobro', 'idasiento', 'fcobro',
        'importe', 'not', 'observaciones',
    ];

    protected $casts = [
        'fcobro' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Cobropro
     * ✅ Usa PK_IDCOBROPRO (indexado)
     */
    public function cobropro()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Cobropro::class, 'IDCOBROPRO', 'IDCOBROPRO');
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
     * Relación: Tcobro
     * ⚠️  SIN ÍNDICE en IDTCOBRO
     */
    public function tcobro()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\Tcobro::class, 'IDTCOBRO', 'IDTCOBRO');
    }

    /**
     * Relación: Asiento
     * ⚠️  SIN ÍNDICE en IDASIENTO
     */
    public function asiento()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\AsientoCent::class, 'IDASIENTO', 'IDASIENTO');
    }

}
