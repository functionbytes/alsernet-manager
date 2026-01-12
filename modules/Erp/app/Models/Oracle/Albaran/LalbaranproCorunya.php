<?php

namespace Modules\Erp\Models\Oracle\Albaran;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LALBARANPRO_CORUNYA
 *
 * ÍNDICES DISPONIBLES:
 * PK_LALBARANPRO_CORUNYA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLALBARANPRO
 *
 */
class LalbaranproCorunya extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lalbaranpro_corunya';
    protected $primaryKey = 'idlalbaranpro';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idmovalm', 'idlpedidopro', 'idalbaranpro', 'idarticulo', 'idusuariomod',
        'unidades', 'not', 'unidalb', 'precio', 'not',
        'dto', 'not', 'iva', 'not', 'recargo',
        'not', 'idtipomedida', 'preciocosto', 'unid', 'notapieza',
        'dto2', 'idlalbaranclireparacion', 'preciomonedaoriginal', 'ubicacion', 'estaubicado',
        'idlalbaranpro_central', 'idalbaranpro_central', 'idalmacen_creacion', 'numero_serie',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lalbaranpro
     * ✅ Usa PK_LALBARANPRO_CORUNYA (indexado)
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
     * ⚠️  SIN ÍNDICE en IDARTICULO
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
     * ⚠️  SIN ÍNDICE en IDLALBARANPRO_CENTRAL
     */
    public function lalbaranproCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\LalbaranproCentral::class, 'IDLALBARANPRO_CENTRAL', 'IDLALBARANPRO_CENTRAL');
    }

    /**
     * Relación: AlbaranproCentral
     * ⚠️  SIN ÍNDICE en IDALBARANPRO_CENTRAL
     */
    public function albaranproCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbaranproCentral::class, 'IDALBARANPRO_CENTRAL', 'IDALBARANPRO_CENTRAL');
    }

}
