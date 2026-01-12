<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla TAG
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_TAG_TABLA (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTABLA
 *
 * PK_TAG (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTAG
 *
 */
class Tag extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'tag';
    protected $primaryKey = 'idtag';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idtabla', 'nombre', 'estado', 'idusuariocre', 'idusuariomod',
        'idusuariobaj', 'observaciones',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Tag
     * ✅ Usa PK_TAG (indexado)
     */
    public function tag()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tag::class, 'IDTAG', 'IDTAG');
    }

    /**
     * Relación: Tabla
     * ✅ Usa IDX_TAG_TABLA (indexado)
     */
    public function tabla()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Tabla::class, 'IDTABLA', 'IDTABLA');
    }

}
