<?php

namespace Modules\Erp\Models\Oracle\Cliente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CLIENTECATALOGO_CENT
 *
 * ÍNDICES DISPONIBLES:
 * ✅ FK_CLIENTECAT_CENT__CLIENTE (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTE
 *
 * PK_CLIENTECATALOGO_CENT (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTECATALOGO
 *
 */
class ClientecatalogoCent extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'clientecatalogo_cent';
    protected $primaryKey = 'idclientecatalogo';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcliente', 'idcatalogo', 'idusuariocre', 'idusuariobaj', 'idusuariomod',
        'estado', 'fsuscripcion',
    ];

    protected $casts = [
        'fsuscripcion' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Clientecatalogo
     * ✅ Usa PK_CLIENTECATALOGO_CENT (indexado)
     */
    public function clientecatalogo()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\ClientecatalogoCent::class, 'IDCLIENTECATALOGO', 'IDCLIENTECATALOGO');
    }

    /**
     * Relación: Cliente
     * ✅ Usa FK_CLIENTECAT_CENT__CLIENTE (indexado)
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
    }

    /**
     * Relación: Catalogo
     * ⚠️  SIN ÍNDICE en IDCATALOGO
     */
    public function catalogo()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\Catalogo::class, 'IDCATALOGO', 'IDCATALOGO');
    }

}
