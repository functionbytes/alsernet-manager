<?php

namespace Modules\Erp\Models\Oracle\Pedido;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla SERIEPEDIDOCLI_DDLEON
 *
 * ÍNDICES DISPONIBLES:
 * PK_SERIEPEDIDOCLI_DDLEON (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDSERIEPEDIDOCLI
 *
 */
class SeriepedidocliDdleon extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'seriepedidocli_ddleon';
    protected $primaryKey = 'idseriepedidocli';
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
     * Relación: Seriepedidocli
     * ✅ Usa PK_SERIEPEDIDOCLI_DDLEON (indexado)
     */
    public function seriepedidocli()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\SeriepedidocliCapthaya::class, 'IDSERIEPEDIDOCLI', 'IDSERIEPEDIDOCLI');
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
