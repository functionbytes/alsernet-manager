<?php

namespace Modules\Erp\Models\Oracle\Lote;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LLLOTE
 *
 * ÍNDICES DISPONIBLES:
 * PK_LLLOTE (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLLLOTE
 *
 */

class Lllote extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lllote';
    protected $primaryKey = 'idlllote';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idllote', 'idarticulo', 'estado', 'unidades', 'idusuariocre',
        'idusuariomod', 'idtipomedida', 'unid',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lllote
     * ✅ Usa PK_LLLOTE (indexado)
     */
    public function lllote()
    {
        return $this->belongsTo(\App\Models\Oracle\Lote\Lllote::class, 'IDLLLOTE', 'IDLLLOTE');
    }

    /**
     * Relación: Llote
     * ⚠️  SIN ÍNDICE en IDLLOTE
     */
    public function llote()
    {
        return $this->belongsTo(\App\Models\Oracle\Lote\Llote::class, 'IDLLOTE', 'IDLLOTE');
    }

    /**
     * Relación: Articulo
     * ⚠️  SIN ÍNDICE en IDARTICULO
     */
    public function articulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: Tipomedida
     * ⚠️  SIN ÍNDICE en IDTIPOMEDIDA
     */
    public function tipomedida()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tipomedida::class, 'IDTIPOMEDIDA', 'IDTIPOMEDIDA');
    }

}
