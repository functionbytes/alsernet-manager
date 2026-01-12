<?php

namespace Modules\Erp\Models\Oracle\Web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla W_PRODUCTO_IMAGEN
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_W_PRODUCTO_IMAGEN_IDPROD (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID_PRODUCTO
 *
 * PK_W_PRODUCTO_IMAGEN (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ID
 *
 */
class WProductoImagen extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'w_producto_imagen';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'id_producto', 'path_imagen', 'orden', 'estado', 'idusuariocre',
        'idusuariomod', 'idusuariobaja', 'idarticulo',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con WProducto
     */
    public function _producto()
    {
        return $this->belongsTo(WProducto::class, 'id_producto', 'idw_producto');
    }


    /**
     * Relación: Producto
     * ✅ Usa IDX_W_PRODUCTO_IMAGEN_IDPROD (indexado)
     */
    public function producto()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WProducto::class, 'ID_PRODUCTO', 'ID');
    }


    /**
     * Relación: WAyudas
     * ✅ Usa PK_W_PRODUCTO_IMAGEN (indexado)
     */
    public function wAyudas()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WAyudas::class, 'ID', 'ID');
    }

    /**
     * Relación: Articulo
     * ⚠️  SIN ÍNDICE en IDARTICULO
     */
    public function articulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

}
