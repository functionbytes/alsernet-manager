<?php

namespace Modules\Erp\Models\Oracle\Cobro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla COBROCLI_DDLEON
 *
 * ÍNDICES DISPONIBLES:
 * PK_COBROCLI_DDLEON (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCOBROCLI
 *
 */
class CobrocliDdleon extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'cobrocli_ddleon';
    protected $primaryKey = 'idcobrocli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idusuariocre', 'idusuariomod', 'idusuariobaj', 'estado', 'importe_cobrado',
        'not', 'importe_libre', 'not', 'fcobro', 'idformapago',
        'idtransportista', 'idvale', 'idcaja', 'idmovcaja', 'idcliente',
        'idasiento', 'segundamano',
    ];

    protected $casts = [
        'fcobro' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Cobrocli
     * ✅ Usa PK_COBROCLI_DDLEON (indexado)
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
