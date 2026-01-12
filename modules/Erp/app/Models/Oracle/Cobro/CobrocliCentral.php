<?php

namespace Modules\Erp\Models\Oracle\Cobro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla COBROCLI_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * PK_COBROCLI_CENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCOBROCLI_CENTRAL
 *
 */
class CobrocliCentral extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'cobrocli_central';
    protected $primaryKey = 'idcobrocli_central';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcobrocli', 'idusuariocre', 'idusuariomod', 'idusuariobaj', 'estado',
        'importe_cobrado', 'not', 'importe_libre', 'not', 'fcobro',
        'idformapago', 'idtransportista', 'idvale', 'idcaja', 'idmovcaja',
        'idcliente', 'idasiento', 'idalmacen_creacion', 'segundamano',
    ];

    protected $casts = [
        'fcobro' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: CobrocliCentral
     * ✅ Usa PK_COBROCLI_CENTRAL (indexado)
     */
    public function cobrocliCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\CobrocliCentral::class, 'IDCOBROCLI_CENTRAL', 'IDCOBROCLI_CENTRAL');
    }

    /**
     * Relación: Cobrocli
     * ⚠️  SIN ÍNDICE en IDCOBROCLI
     */
    public function cobrocli()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\CobrocliCapthaya::class, 'IDCOBROCLI', 'IDCOBROCLI');
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
     * Relación: Transportista
     * ⚠️  SIN ÍNDICE en IDTRANSPORTISTA
     */
    public function transportista()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Transportista::class, 'IDTRANSPORTISTA', 'IDTRANSPORTISTA');
    }

    /**
     * Relación: Vale
     * ⚠️  SIN ÍNDICE en IDVALE
     */
    public function vale()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Vale::class, 'IDVALE', 'IDVALE');
    }

    /**
     * Relación: Cliente
     * ⚠️  SIN ÍNDICE en IDCLIENTE
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
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
