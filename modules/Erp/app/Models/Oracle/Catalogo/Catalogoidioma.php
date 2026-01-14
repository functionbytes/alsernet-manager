<?php

namespace Modules\Erp\Models\Oracle\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CATALOGOIDIOMA
 *
 * ÍNDICES DISPONIBLES:
 * PK_CATALOGOIDIOMA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCATALOGOIDIOMA
 *
 */
class Catalogoidioma extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'catalogoidioma';
    protected $primaryKey = 'idcatalogoidioma';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'ididioma', 'idcatalogo', 'idusuariocre', 'idusuariobaj', 'idusuariomod',
        'estado', 'referencia',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Catalogoidioma
     * ✅ Usa PK_CATALOGOIDIOMA (indexado)
     */
    public function catalogoidioma()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\Catalogoidioma::class, 'IDCATALOGOIDIOMA', 'IDCATALOGOIDIOMA');
    }

    /**
     * Relación: Idioma
     * ⚠️  SIN ÍNDICE en IDIDIOMA
     */
    public function idioma()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Idioma::class, 'IDIDIOMA', 'IDIDIOMA');
    }

    /**
     * Relación: Catalogo
     * ⚠️  SIN ÍNDICE en IDCATALOGO
     */
    public function catalogo()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\Catalogo::class, 'IDCATALOGO', 'IDCATALOGO');
    }

}
