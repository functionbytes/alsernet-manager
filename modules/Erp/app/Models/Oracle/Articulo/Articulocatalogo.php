<?php

namespace Modules\Erp\Models\Oracle\Articulo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla ARTICULOCATALOGO
 *
 * ÍNDICES DISPONIBLES:
 * PK_ARTICULOCATALOGO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTICULOCATALOGO
 *
 */
class Articulocatalogo extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'articulocatalogo';
    protected $primaryKey = 'idarticulocatalogo';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idarticulo', 'idcatalogo', 'idusuariocre', 'idusuariobaj', 'idusuariomod',
        'estado', 'defecto',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Articulocatalogo
     * ✅ Usa PK_ARTICULOCATALOGO (indexado)
     */
    public function articulocatalogo()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Articulo\Articulocatalogo::class, 'IDARTICULOCATALOGO', 'IDARTICULOCATALOGO');
    }

    /**
     * Relación: Articulo
     * ⚠️  SIN ÍNDICE en IDARTICULO
     */
    public function articulo()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: Catalogo
     * ⚠️  SIN ÍNDICE en IDCATALOGO
     */
    public function catalogo()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Catalogo\Catalogo::class, 'IDCATALOGO', 'IDCATALOGO');
    }

}
