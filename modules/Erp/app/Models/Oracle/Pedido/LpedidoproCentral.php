<?php

namespace Modules\Erp\Models\Oracle\Pedido;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LPEDIDOPRO_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * PK_LPEDIDOPRO_CENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPEDIDOPRO_CENTRAL
 *
 */
class LpedidoproCentral extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lpedidopro_central';
    protected $primaryKey = 'idlpedidopro_central';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idlpedidopro', 'idpedidopro_central', 'idpedidopro', 'idarticulo', 'fminentrega',
        'fmaxentrega', 'unidades', 'not', 'precio', 'dto',
        'tipo', 'idusuariomod', 'idtipomedida', 'unid', 'iva',
        'recargo', 'idlpedidocli', 'notapieza', 'dto2', 'preciomonedaoriginal',
        'idlpropuestapro', 'idalmacen_creacion', 'unidades_recomendadas', 'unidades_originales',
    ];

    protected $casts = [
        'fminentrega' => 'datetime',
        'fmaxentrega' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: LpedidoproCentral
     * ✅ Usa PK_LPEDIDOPRO_CENTRAL (indexado)
     */
    public function lpedidoproCentral()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\LpedidoproCentral::class, 'IDLPEDIDOPRO_CENTRAL', 'IDLPEDIDOPRO_CENTRAL');
    }

    /**
     * Relación: Lpedidopro
     * ⚠️  SIN ÍNDICE en IDLPEDIDOPRO
     */
    public function lpedidopro()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\LpedidoproCapthaya::class, 'IDLPEDIDOPRO', 'IDLPEDIDOPRO');
    }

    /**
     * Relación: PedidoproCentral
     * ⚠️  SIN ÍNDICE en IDPEDIDOPRO_CENTRAL
     */
    public function pedidoproCentral()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\PedidoproCentral::class, 'IDPEDIDOPRO_CENTRAL', 'IDPEDIDOPRO_CENTRAL');
    }

    /**
     * Relación: Pedidopro
     * ⚠️  SIN ÍNDICE en IDPEDIDOPRO
     */
    public function pedidopro()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\PedidoproCapthaya::class, 'IDPEDIDOPRO', 'IDPEDIDOPRO');
    }

    /**
     * Relación: Articulo
     * ⚠️  SIN ÍNDICE en IDARTICULO
     */
    public function articulo()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: Tipomedida
     * ⚠️  SIN ÍNDICE en IDTIPOMEDIDA
     */
    public function tipomedida()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Configuracion\Tipomedida::class, 'IDTIPOMEDIDA', 'IDTIPOMEDIDA');
    }

    /**
     * Relación: Lpedidocli
     * ⚠️  SIN ÍNDICE en IDLPEDIDOCLI
     */
    public function lpedidocli()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\LpedidocliCapthaya::class, 'IDLPEDIDOCLI', 'IDLPEDIDOCLI');
    }

    /**
     * Relación: Lpropuestapro
     * ⚠️  SIN ÍNDICE en IDLPROPUESTAPRO
     */
    public function lpropuestapro()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Proveedor\Lpropuestapro::class, 'IDLPROPUESTAPRO', 'IDLPROPUESTAPRO');
    }

}
