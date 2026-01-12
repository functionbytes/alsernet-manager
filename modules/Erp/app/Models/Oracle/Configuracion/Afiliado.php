<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla AFILIADO
 *
 * ÍNDICES DISPONIBLES:
 * PK_AFILIADO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDAFILIADO
 *
 */
class Afiliado extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'afiliado';
    protected $primaryKey = 'idafiliado';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'estado', 'idusuariocre', 'idusuariomod', 'idusuariobaj', 'web',
        'tienda', 'afiliado', 'url_destino',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Afiliado
     * ✅ Usa PK_AFILIADO (indexado)
     */
    public function afiliado()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Afiliado::class, 'IDAFILIADO', 'IDAFILIADO');
    }

}
