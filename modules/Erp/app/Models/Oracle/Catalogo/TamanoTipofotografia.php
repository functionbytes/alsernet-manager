<?php

namespace Modules\Erp\Models\Oracle\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla TAMANO_TIPOFOTOGRAFIA
 *
 * ÍNDICES DISPONIBLES:
 * PK_TAMANO_TIPOFOTOGRAFIA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTAMANO_TIPOFOTOGRAFIA
 *
 * ⚠️  UK_TAMTFOTO_TIPOFOTO_TIPOTAM (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTIPOFOTOGRAFIA, TIPOTAMANO
 *
 */
class TamanoTipofotografia extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'tamano_tipofotografia';
    protected $primaryKey = 'idtamano_tipofotografia';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idusuariocre', 'idusuariomod', 'estado', 'ancho', 'alto',
        'sufijo', 'idtipofotografia', 'tipotamano',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con Tipofotografia
     */
    public function tipofotografia()
    {
        return $this->belongsTo(Tipofotografia::class, 'idtipofotografia', 'idtipofotografia');
    }


    /**
     * Relación: TamanoTipofotografia
     * ✅ Usa PK_TAMANO_TIPOFOTOGRAFIA (indexado)
     */
    public function tamanoTipofotografia()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\TamanoTipofotografia::class, 'IDTAMANO_TIPOFOTOGRAFIA', 'IDTAMANO_TIPOFOTOGRAFIA');
    }

}
