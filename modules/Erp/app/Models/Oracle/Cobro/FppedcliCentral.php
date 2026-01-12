<?php

namespace Modules\Erp\Models\Oracle\Cobro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla FPPEDCLI_CENTRAL (Formas de Pago de Pedidos)
 *
 * @property int $idfppedcli_central Clave primaria (PK)
 * @property int $idpedidocli_central Foreign key a PEDIDOCLI_CENTRAL
 * @property int $idformapago
 * @property float $importe
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_FPPCLI_CENT_IDPEDCLI_CENT (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPEDIDOCLI_CENTRAL
 *
 * PK_FPPEDCLI_CENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDFPPEDCLI_CENTRAL
 *
 */
class FppedcliCentral extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'fppedcli_central';
    protected $primaryKey = 'idfppedcli_central';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idfppedcli', 'idcobrocli_central', 'idcobrocli', 'idpedidocli_central', 'idpedidocli',
        'idformapago', 'idusuariocre', 'idusuariomod', 'idusuariobaj', 'estado',
        'idclientetarjeta', 'importe', 'not', 'fautorizacion', 'desc_autorizacion',
        'idalmacen_creacion', 'idvale', 'pendiente_validacion', 'idusuario_validacion', 'fvalidacion',
        'cobro_confirmado', 'cobro_confirmado_fecha', 'cobro_confirmado_idusuario', 'autorization_id',
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
     * Relación: FppedcliCentral
     * ✅ Usa PK_FPPEDCLI_CENTRAL (indexado)
     */
    public function fppedcliCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\FppedcliCentral::class, 'IDFPPEDCLI_CENTRAL', 'IDFPPEDCLI_CENTRAL');
    }

    /**
     * Relación: Fppedcli
     * ⚠️  SIN ÍNDICE en IDFPPEDCLI
     */
    public function fppedcli()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\FppedcliCapthaya::class, 'IDFPPEDCLI', 'IDFPPEDCLI');
    }

    /**
     * Relación: CobrocliCentral
     * ⚠️  SIN ÍNDICE en IDCOBROCLI_CENTRAL
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
     * Pedido Central (optimizado)
     * ✅ Usa IDX_FPPCLI_CENT_IDPEDCLI_CENT (indexado)
     */
    public function pedido()
    {
        return $this->belongsTo(\App\Models\Oracle\Pedido\PedidocliCentral::class, 'IDPEDIDOCLI_CENTRAL', 'IDPEDIDOCLI_CENTRAL');
    }

    /**
     * Pedido Capthaya (base)
     * ⚠️  SIN ÍNDICE en IDPEDIDOCLI
     */
    public function pedidoCapthaya()
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
