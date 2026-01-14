<?php

namespace Modules\Erp\Models\Oracle\Pedido;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LPEDIDOPRO_SERVIDO_CAPTHAYA
 *
 * ÍNDICES DISPONIBLES:
 * PK_LPEDPRO_SERV_CAPTHAYA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPEDIDOPRO_SERVIDO
 *
 */
class LpedidoproServidoCapthaya extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lpedidopro_servido_capthaya';
    protected $primaryKey = 'idlpedidopro_servido';
    public $timestamps = false;

    protected $fillable = [
        'idlpedidopro', 'unidades_servidas', 'not',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: LpedidoproServido
     * ✅ Usa PK_LPEDPRO_SERV_CAPTHAYA (indexado)
     */
    public function lpedidoproServido()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\LpedidoproServidoCapthaya::class, 'IDLPEDIDOPRO_SERVIDO', 'IDLPEDIDOPRO_SERVIDO');
    }

    /**
     * Relación: Lpedidopro
     * ⚠️  SIN ÍNDICE en IDLPEDIDOPRO
     */
    public function lpedidopro()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\LpedidoproCapthaya::class, 'IDLPEDIDOPRO', 'IDLPEDIDOPRO');
    }

}
