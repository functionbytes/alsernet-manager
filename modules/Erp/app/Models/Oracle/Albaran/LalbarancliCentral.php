<?php

namespace Modules\Erp\Models\Oracle\Albaran;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LALBARANCLI_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_LALBCLICENT_IDALBCLIORIG (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLALBARANCLIORIG, IDALMACEN_CREACION
 *
 * ✅ IDX_LALBCLICENT_IDALBCLIORIG2 (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLALBARANCLIORIG
 *
 * ✅ IDX_LALBCLICENT_IDALB_IDALMCRE (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDALBARANCLI, IDALMACEN_CREACION
 *
 * ✅ IDX_LALBCLICENT_PEDCLICENT (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPEDIDOCLI_CENTRAL
 *
 * ✅ IDX_LALBCLI_CENT_IDALBCLI (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDALBARANCLI_CENTRAL
 *
 * ✅ IDX_LALBCLI_CENT_IDBONO_PRO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDBONO_PROMOCION
 *
 * ✅ IDX_LALBCLI_CENT_IDLPEDCLI (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPEDIDOCLI
 *
 * PK_LALBARANCLI_CENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLALBARANCLI_CENTRAL
 *
 */
class LalbarancliCentral extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lalbarancli_central';
    protected $primaryKey = 'idlalbarancli_central';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idalbarancli_central', 'idlalbarancli', 'idarticulo', 'idmovalm', 'idalbarancli',
        'idusuariomod', 'pcosto', 'precio', 'unidades', 'dto',
        'iva', 'recargo', 'precioorigen', 'idoferta', 'idalmacen',
        'idtipomedida', 'observaciones', 'unid', 'idlote', 'seclote',
        'idlpedidocli', 'notapieza', 'notageneral', 'idlalbarancliorig', 'idtipodescuento',
        'total_bi', 'total_con_impuestos', 'origen_kardex', 'idalmacen_creacion', 'idbono_promocion',
        'guiapertenencia', 'fguiapertenencia', 'narma', 'ngrupo_segundamano', 'total_neto',
        'numero_serie', 'idlpedidocli_central', 'numticket', 'genera_puntos', 'parte_exenta',
        'not', 'tarifa_genera_puntos', 'idempleado_gfitters',
    ];

    protected $casts = [
        'fguiapertenencia' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: LalbarancliCentral
     * ✅ Usa PK_LALBARANCLI_CENTRAL (indexado)
     */
    public function lalbarancliCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\LalbarancliCentral::class, 'IDLALBARANCLI_CENTRAL', 'IDLALBARANCLI_CENTRAL');
    }

    /**
     * Relación: AlbarancliCentral
     * ✅ Usa IDX_LALBCLI_CENT_IDALBCLI (indexado)
     */
    public function albarancliCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbarancliCentral::class, 'IDALBARANCLI_CENTRAL', 'IDALBARANCLI_CENTRAL');
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
     * Relación: Albarancli
     * ✅ Usa IDX_LALBCLICENT_IDALB_IDALMCRE (indexado)
     */
    public function albarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbarancliCapthaya::class, 'IDALBARANCLI', 'IDALBARANCLI');
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
     * Relación: Tipomedida
     * ⚠️  SIN ÍNDICE en IDTIPOMEDIDA
     */
    public function tipomedida()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tipomedida::class, 'IDTIPOMEDIDA', 'IDTIPOMEDIDA');
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
     * Relación: Lpedidocli
     * ✅ Usa IDX_LALBCLI_CENT_IDLPEDCLI (indexado)
     */
    public function lpedidocli()
    {
        return $this->belongsTo(\App\Models\Oracle\Pedido\LpedidocliCapthaya::class, 'IDLPEDIDOCLI', 'IDLPEDIDOCLI');
    }

    /**
     * Relación: Tipodescuento
     * ⚠️  SIN ÍNDICE en IDTIPODESCUENTO
     */
    public function tipodescuento()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Tipodescuento::class, 'IDTIPODESCUENTO', 'IDTIPODESCUENTO');
    }

    /**
     * Relación: BonoPromocion
     * ✅ Usa IDX_LALBCLI_CENT_IDBONO_PRO (indexado)
     */
    public function bonoPromocion()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\BonoPromocion::class, 'IDBONO_PROMOCION', 'IDBONO_PROMOCION');
    }

    /**
     * Relación: LpedidocliCentral
     * ✅ Usa IDX_LALBCLICENT_PEDCLICENT (indexado)
     */
    public function lpedidocliCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Pedido\LpedidocliCentral::class, 'IDLPEDIDOCLI_CENTRAL', 'IDLPEDIDOCLI_CENTRAL');
    }

}
