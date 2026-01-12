<?php

namespace Modules\Erp\Models\Oracle\Pedido;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla PEDIDOPRO_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * PK_PEDIDOPRO_CENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPEDIDOPRO_CENTRAL
 *
 */
class PedidoproCentral extends Model
{
    protected $connection = 'oracle';
    protected $table = 'pedidopro_central';
    protected $primaryKey = 'idpedidopro_central';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idpedidopro', 'idproveedor', 'fminentrega', 'fmaxentrega', 'portes',
        'estado', 'dto', 'npedidopro', 'fpedido', 'idalmacen',
        'idusuariomod', 'idseriepedidopro', 'idseriepedidopro_central', 'npedido', 'idempleado',
        'idregfiscal', 'observaciones', 'idtipopedidoprov', 'tipopedido', 'idconversionmoneda',
        'estpowerpick', 'idalmacen_creacion',
    ];

    protected $casts = [
        'fminentrega' => 'datetime',
        'fmaxentrega' => 'datetime',
        'fpedido' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Pedidopro
     * ⚠️  SIN ÍNDICE en IDPEDIDOPRO
     */
    public function pedidopro()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\PedidoproCapthaya::class, 'IDPEDIDOPRO', 'IDPEDIDOPRO');
    }

    /**
     * Relación: PedidoproCentral
     * ✅ Usa PK_PEDIDOPRO_CENTRAL (indexado)
     */
    public function pedidoproCentral()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\PedidoproCentral::class, 'IDPEDIDOPRO_CENTRAL', 'IDPEDIDOPRO_CENTRAL');
    }

    /**
     * Relación: Proveedor
     * ⚠️  SIN ÍNDICE en IDPROVEEDOR
     */
    public function proveedor()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Proveedor\Proveedor::class, 'IDPROVEEDOR', 'IDPROVEEDOR');
    }

    /**
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

    /**
     * Relación: Seriepedidopro
     * ⚠️  SIN ÍNDICE en IDSERIEPEDIDOPRO
     */
    public function seriepedidopro()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\SeriepedidoproCapthaya::class, 'IDSERIEPEDIDOPRO', 'IDSERIEPEDIDOPRO');
    }

    /**
     * Relación: SeriepedidoproCentral
     * ⚠️  SIN ÍNDICE en IDSERIEPEDIDOPRO_CENTRAL
     */
    public function seriepedidoproCentral()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\SeriepedidoproCentral::class, 'IDSERIEPEDIDOPRO_CENTRAL', 'IDSERIEPEDIDOPRO_CENTRAL');
    }

    /**
     * Relación: Regfiscal
     * ⚠️  SIN ÍNDICE en IDREGFISCAL
     */
    public function regfiscal()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Configuracion\Regfiscal::class, 'IDREGFISCAL', 'IDREGFISCAL');
    }

    /**
     * Relación: Tipopedidoprov
     * ⚠️  SIN ÍNDICE en IDTIPOPEDIDOPROV
     */
    public function tipopedidoprov()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\Tipopedidoproveedor::class, 'IDTIPOPEDIDOPROV', 'IDTIPOPEDIDOPROV');
    }

    /**
     * Relación: Conversionmoneda
     * ⚠️  SIN ÍNDICE en IDCONVERSIONMONEDA
     */
    public function conversionmoneda()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Configuracion\Conversionmoneda::class, 'IDCONVERSIONMONEDA', 'IDCONVERSIONMONEDA');
    }

}
