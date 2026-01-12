<?php

namespace Modules\Erp\Models\Oracle\Cliente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CLIENTETARJETA_CENT
 *
 * ÍNDICES DISPONIBLES:
 * ✅ FK_CLIENTETAR_CENT__CLIENTE (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTE
 *
 * PK_CLIENTETAR_CENT (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTETARJETA
 *
 */
class ClientetarjetaCent extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'clientetarjeta_cent';
    protected $primaryKey = 'idclientetarjeta';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcliente', 'idtarjeta', 'idusuariocre', 'idusuariobaj', 'idusuariomod',
        'estado', 'numerotarjeta', 'idbanco', 'nombretitular', 'fcaducidad',
        'limite', 'idmoneda', 'observacion', 'cvv',
    ];

    protected $casts = [
        'fcaducidad' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Clientetarjeta
     * ✅ Usa PK_CLIENTETAR_CENT (indexado)
     */
    public function clientetarjeta()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\ClientetarjetaCent::class, 'IDCLIENTETARJETA', 'IDCLIENTETARJETA');
    }

    /**
     * Relación: Cliente
     * ✅ Usa FK_CLIENTETAR_CENT__CLIENTE (indexado)
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
    }

    /**
     * Relación: Tarjeta
     * ⚠️  SIN ÍNDICE en IDTARJETA
     */
    public function tarjeta()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Tarjetas::class, 'IDTARJETA', 'IDTARJETA');
    }

    /**
     * Relación: Banco
     * ⚠️  SIN ÍNDICE en IDBANCO
     */
    public function banco()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Banco::class, 'IDBANCO', 'IDBANCO');
    }

    /**
     * Relación: Moneda
     * ⚠️  SIN ÍNDICE en IDMONEDA
     */
    public function moneda()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Moneda::class, 'IDMONEDA', 'IDMONEDA');
    }

}
