<?php

namespace Modules\Erp\Models\Oracle\Albaran;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LALBARANPRO_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_IDLALBPRO_CENT_ALBPRO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDALBARANPRO_CENTRAL
 *
 * ✅ IDX_IDLALBPRO_CENT_ART (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTICULO
 *
 * PK_LALBARANPRO_CENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLALBARANPRO_CENTRAL
 *
 */
class LalbaranproCentral extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lalbaranpro_central';
    protected $primaryKey = 'idlalbaranpro_central';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idlalbaranpro', 'idmovalm', 'idlpedidopro', 'idalbaranpro', 'idarticulo',
        'idusuariomod', 'unidades', 'unidalb', 'precio', 'dto',
        'iva', 'recargo', 'idtipomedida', 'preciocosto', 'unid',
        'notapieza', 'dto2', 'idlalbaranclireparacion', 'preciomonedaoriginal', 'idalbaranpro_central',
        'idalmacen_creacion', 'idlpedidopro_central', 'ubicacion', 'estaubicado', 'numero_serie',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lalbaranpro
     * ⚠️  SIN ÍNDICE en IDLALBARANPRO
     */
    public function lalbaranpro()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\LalbaranproCapthaya::class, 'IDLALBARANPRO', 'IDLALBARANPRO');
    }

    /**
     * Relación: Lpedidopro
     * ⚠️  SIN ÍNDICE en IDLPEDIDOPRO
     */
    public function lpedidopro()
    {
        return $this->belongsTo(\App\Models\Oracle\Pedido\LpedidoproCapthaya::class, 'IDLPEDIDOPRO', 'IDLPEDIDOPRO');
    }

    /**
     * Relación: Albaranpro
     * ⚠️  SIN ÍNDICE en IDALBARANPRO
     */
    public function albaranpro()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbaranproCapthaya::class, 'IDALBARANPRO', 'IDALBARANPRO');
    }

    /**
     * Relación: Articulo
     * ✅ Usa IDX_IDLALBPRO_CENT_ART (indexado)
     */
    public function articulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: Tipomedida
     * ⚠️  SIN ÍNDICE en IDTIPOMEDIDA
     */
    public function tipomedida()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tipomedida::class, 'IDTIPOMEDIDA', 'IDTIPOMEDIDA');
    }

    /**
     * Relación: LalbaranproCentral
     * ✅ Usa PK_LALBARANPRO_CENTRAL (indexado)
     */
    public function lalbaranproCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\LalbaranproCentral::class, 'IDLALBARANPRO_CENTRAL', 'IDLALBARANPRO_CENTRAL');
    }

    /**
     * Relación: AlbaranproCentral
     * ✅ Usa IDX_IDLALBPRO_CENT_ALBPRO (indexado)
     */
    public function albaranproCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbaranproCentral::class, 'IDALBARANPRO_CENTRAL', 'IDALBARANPRO_CENTRAL');
    }

    /**
     * Relación: LpedidoproCentral
     * ⚠️  SIN ÍNDICE en IDLPEDIDOPRO_CENTRAL
     */
    public function lpedidoproCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Pedido\LpedidoproCentral::class, 'IDLPEDIDOPRO_CENTRAL', 'IDLPEDIDOPRO_CENTRAL');
    }

}
