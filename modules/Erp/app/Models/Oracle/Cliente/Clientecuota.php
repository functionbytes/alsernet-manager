<?php

namespace Modules\Erp\Models\Oracle\Cliente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CLIENTECUOTA
 *
 * ÍNDICES DISPONIBLES:
 * PK_CLIENTECUOTA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTECUOTA
 *
 */
class Clientecuota extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'clientecuota';
    protected $primaryKey = 'idclientecuota';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcliente', 'idlpedidocli', 'idalmacen', 'idarticulo', 'fcontratacion',
        'ffinservicio', 'importe', 'not', 'estado', 'idusuariocre',
        'idusuariomod', 'idusuariobaj', 'idclientecuenta',
    ];

    protected $casts = [
        'fcontratacion' => 'datetime',
        'ffinservicio' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Clientecuota
     * ✅ Usa PK_CLIENTECUOTA (indexado)
     */
    public function clientecuota()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Clientecuota::class, 'IDCLIENTECUOTA', 'IDCLIENTECUOTA');
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
     * Relación: Lpedidocli
     * ⚠️  SIN ÍNDICE en IDLPEDIDOCLI
     */
    public function lpedidocli()
    {
        return $this->belongsTo(\App\Models\Oracle\Pedido\LpedidocliCapthaya::class, 'IDLPEDIDOCLI', 'IDLPEDIDOCLI');
    }

    /**
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

    /**
     * Relación: Articulo
     * ⚠️  SIN ÍNDICE en IDARTICULO
     */
    public function articulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: Clientecuenta
     * ⚠️  SIN ÍNDICE en IDCLIENTECUENTA
     */
    public function clientecuenta()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\ClientecuentaCent::class, 'IDCLIENTECUENTA', 'IDCLIENTECUENTA');
    }

}
