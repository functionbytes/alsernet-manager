<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla ORDMFILTRO
 *
 * ÍNDICES DISPONIBLES:
 * PK_ORDMFILTRO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDORDMFILTRO
 *
 */
class Ordmfiltro extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'ordmfiltro';
    protected $primaryKey = 'idordmfiltro';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcampo', 'idmfiltro', 'estado', 'idusuariomod', 'nombre',
        'descripcion', 'visible',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Ordmfiltro
     * ✅ Usa PK_ORDMFILTRO (indexado)
     */
    public function ordmfiltro()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Ordmfiltro::class, 'IDORDMFILTRO', 'IDORDMFILTRO');
    }

    /**
     * Relación: Campo
     * ⚠️  SIN ÍNDICE en IDCAMPO
     */
    public function campo()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Campo::class, 'IDCAMPO', 'IDCAMPO');
    }

    /**
     * Relación: Mfiltro
     * ⚠️  SIN ÍNDICE en IDMFILTRO
     */
    public function mfiltro()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Mfiltro::class, 'IDMFILTRO', 'IDMFILTRO');
    }

}
