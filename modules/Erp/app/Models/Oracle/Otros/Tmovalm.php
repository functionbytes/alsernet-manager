<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla TMOVALM
 *
 * ÍNDICES DISPONIBLES:
 * PK_TMOVALM (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTMOVALM
 *
 */
class Tmovalm extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'tmovalm';
    protected $primaryKey = 'idtmovalm';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idteststock', 'tes_idteststock', 'estado', 'descripcion', 'oporigen',
        'opdestino', 'parausuario',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Tmovalm
     * ✅ Usa PK_TMOVALM (indexado)
     */
    public function tmovalm()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Tmovalm::class, 'IDTMOVALM', 'IDTMOVALM');
    }

    /**
     * Relación: Teststock
     * ⚠️  SIN ÍNDICE en IDTESTSTOCK
     */
    public function teststock()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Teststock::class, 'IDTESTSTOCK', 'IDTESTSTOCK');
    }

}
