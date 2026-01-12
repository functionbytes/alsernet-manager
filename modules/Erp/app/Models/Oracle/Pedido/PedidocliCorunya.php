<?php

namespace Modules\Erp\Models\Oracle\Pedido;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla PEDIDOCLI_CORUNYA
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_PEDCLI_COR_ESTADO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ESTADO
 *
 * ✅ IDX_PEDCLI_COR_FPEDIDO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: FPEDIDO
 *
 * PK_PEDIDOCLI_CORUNYA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPEDIDOCLI
 *
 */
class PedidocliCorunya extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'pedidocli_corunya';
    protected $primaryKey = 'idpedidocli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idalmacen', 'idcliente', 'estado', 'fpedido', 'fcomreserva',
        'fliberacion', 'observaciones', 'idusuariomod', 'idregfiscal', 'idempleado',
        'idseriepedidocli', 'npedidocli', 'tiporiesgo', 'idprioridad', 'idenvio',
        'idorigenpedidocli', 'idusuariocre', 'idusuariobaj', 'idcatalogo', 'idultimaincidencia',
        'fprevista', 'fservido', 'clientetelefono', 'numeroserie', 'identificadororigen',
        'solicitafactura', 'concartuchos', 'servirincompleto', 'facturado', 'revisadotransp',
        'tipopedido', 'idregpais', 'idtmotivoanulacionpedido', 'idafiliado', 'texto_regalo',
        'idclientecuenta', 'es_compromiso_alvarez', 'idprefijo_telefono',
    ];

    protected $casts = [
        'fpedido' => 'datetime',
        'fcomreserva' => 'datetime',
        'fliberacion' => 'datetime',
        'fprevista' => 'datetime',
        'fservido' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Pedido
     * ✅ Usa PK_PEDIDOCLI_CORUNYA (indexado)
     */
    public function pedido()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\PedidocliCapthaya::class, 'IDPEDIDOCLI', 'IDPEDIDOCLI');
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
     * Relación: Cliente
     * ⚠️  SIN ÍNDICE en IDCLIENTE
     */
    public function cliente()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
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
     * Relación: Seriepedidocli
     * ⚠️  SIN ÍNDICE en IDSERIEPEDIDOCLI
     */
    public function seriepedidocli()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\SeriepedidocliCapthaya::class, 'IDSERIEPEDIDOCLI', 'IDSERIEPEDIDOCLI');
    }

    /**
     * Relación: Prioridad
     * ⚠️  SIN ÍNDICE en IDPRIORIDAD
     */
    public function prioridad()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Otros\Prioridad::class, 'IDPRIORIDAD', 'IDPRIORIDAD');
    }

    /**
     * Relación: Origenpedidocli
     * ⚠️  SIN ÍNDICE en IDORIGENPEDIDOCLI
     */
    public function origenpedidocli()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\Origenpedidocli::class, 'IDORIGENPEDIDOCLI', 'IDORIGENPEDIDOCLI');
    }

    /**
     * Relación: Catalogo
     * ⚠️  SIN ÍNDICE en IDCATALOGO
     */
    public function catalogo()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Catalogo\Catalogo::class, 'IDCATALOGO', 'IDCATALOGO');
    }

    /**
     * Relación: Regpais
     * ⚠️  SIN ÍNDICE en IDREGPAIS
     */
    public function regpais()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Configuracion\Regpais::class, 'IDREGPAIS', 'IDREGPAIS');
    }

    /**
     * Relación: Tmotivoanulacionpedido
     * ⚠️  SIN ÍNDICE en IDTMOTIVOANULACIONPEDIDO
     */
    public function tmotivoanulacionpedido()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\Tmotivoanulacionpedido::class, 'IDTMOTIVOANULACIONPEDIDO', 'IDTMOTIVOANULACIONPEDIDO');
    }

    /**
     * Relación: Afiliado
     * ⚠️  SIN ÍNDICE en IDAFILIADO
     */
    public function afiliado()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Configuracion\Afiliado::class, 'IDAFILIADO', 'IDAFILIADO');
    }

    /**
     * Relación: Clientecuenta
     * ⚠️  SIN ÍNDICE en IDCLIENTECUENTA
     */
    public function clientecuenta()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Cliente\ClientecuentaCent::class, 'IDCLIENTECUENTA', 'IDCLIENTECUENTA');
    }

    /**
     * Relación: PrefijoTelefono
     * ⚠️  SIN ÍNDICE en IDPREFIJO_TELEFONO
     */
    public function prefijoTelefono()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Otros\PrefijoTelefono::class, 'IDPREFIJO_TELEFONO', 'IDPREFIJO_TELEFONO');
    }

}
