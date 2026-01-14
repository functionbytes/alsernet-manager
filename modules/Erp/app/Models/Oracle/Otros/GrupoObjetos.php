<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla GRUPO_OBJETOS
 *
 * ÍNDICES DISPONIBLES:
 * PK_GRUPO_OBJETOS (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDGRUPOOBJETO
 *
 */
class GrupoObjetos extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'grupo_objetos';
    protected $primaryKey = 'idgrupoobjeto';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'estado', 'idusuariomod', 'nombre', 'descripcion', 'nivel',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Grupoobjeto
     * ✅ Usa PK_GRUPO_OBJETOS (indexado)
     */
    public function grupoobjeto()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\GrupoObjetos::class, 'IDGRUPOOBJETO', 'IDGRUPOOBJETO');
    }

}
