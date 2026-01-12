<?php

namespace Modules\Erp\Models\Oracle\Pedido;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LPEDIDOPRO_SERVIDO_DDLEON
 *
 * ÍNDICES DISPONIBLES:
 * PK_LPEDPRO_SERV_DDLEON (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPEDIDOPRO_SERVIDO
 *
 */
class LpedidoproServidoDdleon extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lpedidopro_servido_ddleon';
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
     * ✅ Usa PK_LPEDPRO_SERV_DDLEON (indexado)
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
