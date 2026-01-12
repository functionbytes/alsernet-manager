<?php

namespace Modules\Erp\Models\Oracle\Pedido;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla SERIEPEDIDOPRO_TPVCOR
 *
 * ÍNDICES DISPONIBLES:
 * PK_SERIEPEDIDOPRO_TPVCOR (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDSERIEPEDIDOPRO
 *
 */
class SeriepedidoproTpvcor extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'seriepedidopro_tpvcor';
    protected $primaryKey = 'idseriepedidopro';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'descripcion', 'descripcorta', 'idusuariocre', 'idusuariomod', 'idusuariobaj',
        'estado', 'numero', 'idempresa', 'fdesde', 'fhasta',
    ];

    protected $casts = [
        'fdesde' => 'datetime',
        'fhasta' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Seriepedidopro
     * ✅ Usa PK_SERIEPEDIDOPRO_TPVCOR (indexado)
     */
    public function seriepedidopro()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\SeriepedidoproCapthaya::class, 'IDSERIEPEDIDOPRO', 'IDSERIEPEDIDOPRO');
    }

    /**
     * Relación: Empresa
     * ⚠️  SIN ÍNDICE en IDEMPRESA
     */
    public function empresa()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Otros\Empresa::class, 'IDEMPRESA', 'IDEMPRESA');
    }

}
