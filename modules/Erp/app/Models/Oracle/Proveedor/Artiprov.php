<?php

namespace Modules\Erp\Models\Oracle\Proveedor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla ARTIPROV
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_ARTIPROV_EAN13 (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: EAN13
 *
 * ✅ IDX_ARTIPROV_IDARTICULO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTICULO
 *
 * ✅ IDX_ARTIPROV_IDPROVEEDOR (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPROVEEDOR
 *
 * ✅ IDX_ARTIPROV_UPC (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: UPC
 *
 * PK_ARTIPROV (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTIPROV
 *
 */
class Artiprov extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'artiprov';
    protected $primaryKey = 'idartiprov';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idproveedor', 'idarticulo', 'descripcion', 'codigo', 'pcosto',
        'dto1', 'dto2', 'idusuariocre', 'idusuariomod', 'idusuariobaj',
        'estado', 'pordefecto', 'ean13', 'upc', 'codigo2',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Artiprov
     * ✅ Usa PK_ARTIPROV (indexado)
     */
    public function artiprov()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Artiprov::class, 'IDARTIPROV', 'IDARTIPROV');
    }

    /**
     * Relación: Proveedor
     * ✅ Usa IDX_ARTIPROV_IDPROVEEDOR (indexado)
     */
    public function proveedor()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Proveedor::class, 'IDPROVEEDOR', 'IDPROVEEDOR');
    }

    /**
     * Relación: Articulo
     * ✅ Usa IDX_ARTIPROV_IDARTICULO (indexado)
     */
    public function articulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

}
