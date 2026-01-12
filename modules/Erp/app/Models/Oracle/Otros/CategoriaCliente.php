<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CATEGORIA_CLIENTE
 *
 * ÍNDICES DISPONIBLES:
 * PK_CATEGORIA_CLIENTE (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCATEGORIA_CLIENTE
 *
 */
class CategoriaCliente extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'categoria_cliente';
    protected $primaryKey = 'idcategoria_cliente';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'descripcion', 'estado', 'idusuariocre', 'idusuariomod', 'idusuariobaj',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: CategoriaCliente
     * ✅ Usa PK_CATEGORIA_CLIENTE (indexado)
     */
    public function categoriaCliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\CategoriaCliente::class, 'IDCATEGORIA_CLIENTE', 'IDCATEGORIA_CLIENTE');
    }

}
