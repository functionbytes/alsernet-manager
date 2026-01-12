<?php

namespace Modules\Erp\Models\Oracle\Pedido;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LPEDIDOCLI_TPVCOR
 *
 * ÍNDICES DISPONIBLES:
 * PK_LPEDIDOCLI_TPVCOR (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPEDIDOCLI
 *
 */
class Lpedidocli extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lpedidocli_tpvcor';
    protected $primaryKey = 'idlpedidocli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idpedidocli', 'idmovalm', 'idarticulo', 'estado', 'unidades',
        'not', 'freserva', 'fliberacion', 'idusuariomod', 'pcosto',
        'precio', 'dto', 'iva', 'recargo', 'idtipomedida',
        'idlpresupuestocli', 'unid', 'idlote', 'seclote', 'notapieza',
        'idlalbaranpro', 'notageneral', 'idlpedidocli_internet', 'idbono_promocion', 'guiapertenencia',
        'fguiapertenencia', 'ubicacion', 'ngrupo_segundamano', 'parte_exenta', 'not',
        'tarifa_genera_puntos', 'idcatalogo', 'idlpedidodel', 'idalmacen_forzar_pedir',
    ];

    protected $casts = [
        'freserva' => 'datetime',
        'fliberacion' => 'datetime',
        'fguiapertenencia' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lpedidocli
     * ✅ Usa PK_LPEDIDOCLI_TPVCOR (indexado)
     */
    public function lpedidocli()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\LpedidocliCapthaya::class, 'IDLPEDIDOCLI', 'IDLPEDIDOCLI');
    }

    /**
     * Relación: Pedido
     * ⚠️  SIN ÍNDICE en IDPEDIDOCLI
     */
    public function pedido()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Pedido\PedidocliCapthaya::class, 'IDPEDIDOCLI', 'IDPEDIDOCLI');
    }

    /**
     * Relación: Articulo
     * ⚠️  SIN ÍNDICE en IDARTICULO
     */
    public function articulo()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: Tipomedida
     * ⚠️  SIN ÍNDICE en IDTIPOMEDIDA
     */
    public function tipomedida()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Configuracion\Tipomedida::class, 'IDTIPOMEDIDA', 'IDTIPOMEDIDA');
    }

    /**
     * Relación: Lote
     * ⚠️  SIN ÍNDICE en IDLOTE
     */
    public function lote()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Lote\Lote::class, 'IDLOTE', 'IDLOTE');
    }

    /**
     * Relación: Lalbaranpro
     * ⚠️  SIN ÍNDICE en IDLALBARANPRO
     */
    public function lalbaranpro()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Albaran\LalbaranproCapthaya::class, 'IDLALBARANPRO', 'IDLALBARANPRO');
    }

    /**
     * Relación: BonoPromocion
     * ⚠️  SIN ÍNDICE en IDBONO_PROMOCION
     */
    public function bonoPromocion()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Promocion\BonoPromocion::class, 'IDBONO_PROMOCION', 'IDBONO_PROMOCION');
    }

    /**
     * Relación: Catalogo
     * ⚠️  SIN ÍNDICE en IDCATALOGO
     */
    public function catalogo()
    {
        return $this->belongsTo(\Modules\Erp\Models\Oracle\Catalogo\Catalogo::class, 'IDCATALOGO', 'IDCATALOGO');
    }

}
