<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LMFILTRO
 *
 * ÍNDICES DISPONIBLES:
 * PK_LMFILTRO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLMFILTRO
 *
 */
class Lmfiltro extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lmfiltro';
    protected $primaryKey = 'idlmfiltro';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idtipo', 'idmfiltro', 'idcampo', 'estado', 'idusuariomod',
        'descripcion', 'visible', 'codigo', 'tipo', 'longitud',
        'decimales', 'sufijo', 'orden', 'mostrarlookup', 'modolike',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lmfiltro
     * ✅ Usa PK_LMFILTRO (indexado)
     */
    public function lmfiltro()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Lmfiltro::class, 'IDLMFILTRO', 'IDLMFILTRO');
    }

    /**
     * Relación: Mfiltro
     * ⚠️  SIN ÍNDICE en IDMFILTRO
     */
    public function mfiltro()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Mfiltro::class, 'IDMFILTRO', 'IDMFILTRO');
    }

    /**
     * Relación: Campo
     * ⚠️  SIN ÍNDICE en IDCAMPO
     */
    public function campo()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Campo::class, 'IDCAMPO', 'IDCAMPO');
    }

}
