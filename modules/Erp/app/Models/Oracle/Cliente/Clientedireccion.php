<?php

namespace Modules\Erp\Models\Oracle\Cliente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CLIENTEDIRECCION_CENT
 *
 * ÍNDICES DISPONIBLES:
 * ✅ FK_CLIENTEDIR_CENT__CLIENTE (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTE
 *
 * PK_CLIENTEDIR_CENT (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTEDIRECCION
 *
 */
class Clientedireccion extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'clientedireccion_cent';
    protected $primaryKey = 'idclientedireccion';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcliente', 'idtipodireccion', 'idusuariocre', 'idusuariobaj', 'idusuariomod',
        'estado', 'codigopostal', 'poblacion', 'provincia', 'pais',
        'observacion', 'calle', 'num',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Clientedireccion
     * ✅ Usa PK_CLIENTEDIR_CENT (indexado)
     */
    public function clientedireccion()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Clientedireccion::class, 'IDCLIENTEDIRECCION', 'IDCLIENTEDIRECCION');
    }

    /**
     * Relación: Cliente
     * ✅ Usa FK_CLIENTEDIR_CENT__CLIENTE (indexado)
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
    }

    /**
     * Relación: Tipodireccion
     * ⚠️  SIN ÍNDICE en IDTIPODIRECCION
     */
    public function tipodireccion()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tipodireccion::class, 'IDTIPODIRECCION', 'IDTIPODIRECCION');
    }

}
