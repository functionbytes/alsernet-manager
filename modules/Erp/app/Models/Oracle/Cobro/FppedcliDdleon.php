<?php

namespace Modules\Erp\Models\Oracle\Cobro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla FPPEDCLI_DDLEON
 *
 * ÍNDICES DISPONIBLES:
 * PK_FPPEDCLI_DDLEON (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDFPPEDCLI
 *
 */
class FppedcliDdleon extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'fppedcli_ddleon';
    protected $primaryKey = 'idfppedcli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcobrocli', 'idpedidocli', 'idformapago', 'idusuariocre', 'idusuariomod',
        'idusuariobaj', 'estado', 'idclientetarjeta', 'importe', 'not',
        'fautorizacion', 'desc_autorizacion', 'nplazos', 'nsolicitud_vip', 'idvale',
        'pendiente_validacion', 'idusuario_validacion', 'fvalidacion', 'cobro_confirmado', 'cobro_confirmado_fecha',
        'cobro_confirmado_idusuario', 'autorization_id',
    ];

    protected $casts = [
        'fautorizacion' => 'datetime',
        'fvalidacion' => 'datetime',
        'cobro_confirmado_fecha' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Fppedcli
     * ✅ Usa PK_FPPEDCLI_DDLEON (indexado)
     */
    public function fppedcli()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\FppedcliCapthaya::class, 'IDFPPEDCLI', 'IDFPPEDCLI');
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
     * Relación: Pedido
     * ⚠️  SIN ÍNDICE en IDPEDIDOCLI
     */
    public function pedido()
    {
        return $this->belongsTo(\App\Models\Oracle\Pedido\PedidocliCapthaya::class, 'IDPEDIDOCLI', 'IDPEDIDOCLI');
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
     * Relación: Clientetarjeta
     * ⚠️  SIN ÍNDICE en IDCLIENTETARJETA
     */
    public function clientetarjeta()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\ClientetarjetaCent::class, 'IDCLIENTETARJETA', 'IDCLIENTETARJETA');
    }

    /**
     * Relación: Vale
     * ⚠️  SIN ÍNDICE en IDVALE
     */
    public function vale()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Vale::class, 'IDVALE', 'IDVALE');
    }

}
