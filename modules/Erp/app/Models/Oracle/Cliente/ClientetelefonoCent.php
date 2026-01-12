<?php

namespace Modules\Erp\Models\Oracle\Cliente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CLIENTETELEFONO_CENT
 *
 * ÍNDICES DISPONIBLES:
 * ✅ FK_CLIENTETEL_CENT__CLIENTE (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTE
 *
 * PK_CLIENTETEL_CENT (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTETELEFONO
 *
 */
class ClientetelefonoCent extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'clientetelefono_cent';
    protected $primaryKey = 'idclientetelefono';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcliente', 'idtipotelefono', 'idusuariocre', 'idusuariobaj', 'idusuariomod',
        'estado', 'telefono', 'horario', 'observacion', 'envio_sms',
        'idprefijo_telefono',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Clientetelefono
     * ✅ Usa PK_CLIENTETEL_CENT (indexado)
     */
    public function clientetelefono()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\ClientetelefonoCent::class, 'IDCLIENTETELEFONO', 'IDCLIENTETELEFONO');
    }

    /**
     * Relación: Cliente
     * ✅ Usa FK_CLIENTETEL_CENT__CLIENTE (indexado)
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
    }

    /**
     * Relación: Tipotelefono
     * ⚠️  SIN ÍNDICE en IDTIPOTELEFONO
     */
    public function tipotelefono()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tipotelefono::class, 'IDTIPOTELEFONO', 'IDTIPOTELEFONO');
    }

    /**
     * Relación: PrefijoTelefono
     * ⚠️  SIN ÍNDICE en IDPREFIJO_TELEFONO
     */
    public function prefijoTelefono()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\PrefijoTelefono::class, 'IDPREFIJO_TELEFONO', 'IDPREFIJO_TELEFONO');
    }

}
