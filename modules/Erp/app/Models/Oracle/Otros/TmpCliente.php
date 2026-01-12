<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla TMP_CLIENTE
 */
class TmpCliente extends Model
{
    protected $connection = 'oracle';
    protected $table = 'tmp_cliente';
    protected $primaryKey = 'idcliente';
    public $timestamps = false;

    protected $fillable = [
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Cliente
     * ⚠️  SIN ÍNDICE en IDCLIENTE
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
    }

}
