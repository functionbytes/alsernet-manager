<?php

namespace Modules\Erp\Models\Oracle\Articulo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla ARTICULOIDIOMA
 *
 * ÍNDICES DISPONIBLES:
 * PK_ARTICULOIDIOMA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTICULOIDIOMA
 *
 */
class Articuloidioma extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'articuloidioma';
    protected $primaryKey = 'idarticuloidioma';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idarticulo', 'ididioma', 'idusuariocre', 'idusuariobaj', 'idusuariomod',
        'estado', 'descripcion', 'descripcioncorta',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Articuloidioma
     * ✅ Usa PK_ARTICULOIDIOMA (indexado)
     */
    public function articuloidioma()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Articulo\Articuloidioma::class, 'IDARTICULOIDIOMA', 'IDARTICULOIDIOMA');
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
     * Relación: Idioma
     * ⚠️  SIN ÍNDICE en IDIDIOMA
     */
    public function idioma()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Configuracion\Idioma::class, 'IDIDIOMA', 'IDIDIOMA');
    }

}
