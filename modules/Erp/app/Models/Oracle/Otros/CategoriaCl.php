<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CATEGORIA_CL
 *
 * ÍNDICES DISPONIBLES:
 * PK_CATEGORIA_CL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCATEGORIA_CL
 *
 */
class CategoriaCl extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'categoria_cl';
    protected $primaryKey = 'idcategoria_cl';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'iddeporte_cl', 'estado', 'idusuariocre', 'idusuariomod', 'idusuariobaj',
        'descripcion', 'desc_corta', 'aparece_inf_stock',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: CategoriaCl
     * ✅ Usa PK_CATEGORIA_CL (indexado)
     */
    public function categoriaCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\CategoriaCl::class, 'IDCATEGORIA_CL', 'IDCATEGORIA_CL');
    }

    /**
     * Relación: DeporteCl
     * ⚠️  SIN ÍNDICE en IDDEPORTE_CL
     */
    public function deporteCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\DeporteCl::class, 'IDDEPORTE_CL', 'IDDEPORTE_CL');
    }

}
