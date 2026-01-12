<?php

namespace Modules\Erp\Models\Oracle\Pedido;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LPEDIDOPRO_SERVIDO_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * PK_IDLPEDPRO_SERVIDO_CENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPEDIDOPRO_SERVIDO_CENTRAL
 *
 */
class LpedidoproServidoCentral extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lpedidopro_servido_central';
    protected $primaryKey = 'idlpedidopro_servido_central';
    public $timestamps = false;

    protected $fillable = [
        'idlpedidopro_servido', 'idlpedidopro', 'unidades_servidas', 'not', 'idlpedidopro_central',
        'idalmacen_creacion',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: LpedidoproServido
     * ⚠️  SIN ÍNDICE en IDLPEDIDOPRO_SERVIDO
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

    /**
     * Relación: LpedidoproCentral
     * ⚠️  SIN ÍNDICE en IDLPEDIDOPRO_CENTRAL
     */
    public function lpedidoproCentral()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\LpedidoproCentral::class, 'IDLPEDIDOPRO_CENTRAL', 'IDLPEDIDOPRO_CENTRAL');
    }

    /**
     * Relación: LpedidoproServidoCentral
     * ✅ Usa PK_IDLPEDPRO_SERVIDO_CENTRAL (indexado)
     */
    public function lpedidoproServidoCentral()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\LpedidoproServidoCentral::class, 'IDLPEDIDOPRO_SERVIDO_CENTRAL', 'IDLPEDIDOPRO_SERVIDO_CENTRAL');
    }

}
