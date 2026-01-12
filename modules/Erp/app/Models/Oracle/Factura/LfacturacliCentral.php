<?php

namespace Modules\Erp\Models\Oracle\Factura;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LFACTURACLI_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_FACTURACLICENT_IDLALBCENT (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLALBARANCLI_CENTRAL
 *
 * ✅ INDX_LFACTURACLI_IDFACLI (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDFACTURACLI
 *
 * PK_LFACTURACLICENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLFACTURACLI
 *
 */
class LfacturacliCentral extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lfacturacli_central';
    protected $primaryKey = 'idlfacturacli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idfacturacli', 'idlalbarancli', 'idarticulo', 'unidades', 'not',
        'iva', 'not', 'recargo', 'not', 'pbi',
        'not', 'dto', 'not', 'idusuariomod', 'codigo',
        'descripcion', 'dtocabecera', 'idtipomedida', 'unid', 'idlpedidocli',
        'idlote', 'seclote', 'total_bi', 'total_con_impuestos', 'idalmacen',
        'idlalbarancli_central', 'ngrupo_segundamano', 'parte_exenta', 'not', 'nexpediente',
        'fexpediente', 'numero_serie',
    ];

    protected $casts = [
        'fexpediente' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lfacturacli
     * ✅ Usa PK_LFACTURACLICENTRAL (indexado)
     */
    public function lfacturacli()
    {
        return $this->belongsTo(\App\Models\Oracle\Factura\LfacturacliCentral::class, 'IDLFACTURACLI', 'IDLFACTURACLI');
    }

    /**
     * Relación: Facturacli
     * ✅ Usa INDX_LFACTURACLI_IDFACLI (indexado)
     */
    public function facturacli()
    {
        return $this->belongsTo(\App\Models\Oracle\Factura\FacturacliCentral::class, 'IDFACTURACLI', 'IDFACTURACLI');
    }

    /**
     * Relación: Lalbarancli
     * ⚠️  SIN ÍNDICE en IDLALBARANCLI
     */
    public function lalbarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\LalbarancliCapthaya::class, 'IDLALBARANCLI', 'IDLALBARANCLI');
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
     * Relación: Lpedidocli
     * ⚠️  SIN ÍNDICE en IDLPEDIDOCLI
     */
    public function lpedidocli()
    {
        return $this->belongsTo(\App\Models\Oracle\Pedido\LpedidocliCapthaya::class, 'IDLPEDIDOCLI', 'IDLPEDIDOCLI');
    }

    /**
     * Relación: Lote
     * ⚠️  SIN ÍNDICE en IDLOTE
     */
    public function lote()
    {
        return $this->belongsTo(\App\Models\Oracle\Lote\Lote::class, 'IDLOTE', 'IDLOTE');
    }

    /**
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

    /**
     * Relación: LalbarancliCentral
     * ✅ Usa IDX_FACTURACLICENT_IDLALBCENT (indexado)
     */
    public function lalbarancliCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\LalbarancliCentral::class, 'IDLALBARANCLI_CENTRAL', 'IDLALBARANCLI_CENTRAL');
    }

}
