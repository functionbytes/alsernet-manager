<?php

namespace Modules\Erp\Models\Oracle\Cliente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CLIENTEGUIA
 *
 * ÍNDICES DISPONIBLES:
 * PK_CLIENTEGUIA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTEGUIA
 *
 */
class Clienteguia extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'clienteguia';
    protected $primaryKey = 'idclienteguia';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcliente', 'descripcion', 'fguia', 'nguia', 'narma',
        'estado', 'idusuariocre', 'idusuariomod', 'idusuariobaj',
    ];

    protected $casts = [
        'fguia' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Clienteguia
     * ✅ Usa PK_CLIENTEGUIA (indexado)
     */
    public function clienteguia()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Clienteguia::class, 'IDCLIENTEGUIA', 'IDCLIENTEGUIA');
    }

    /**
     * Relación: Cliente
     * ⚠️  SIN ÍNDICE en IDCLIENTE
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
    }

}
