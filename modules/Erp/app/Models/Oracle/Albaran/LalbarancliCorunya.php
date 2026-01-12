<?php

namespace Modules\Erp\Models\Oracle\Albaran;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LALBARANCLI_CORUNYA
 *
 * ÍNDICES DISPONIBLES:
 * PK_LALBARANCLI_CORUNYA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLALBARANCLI
 *
 */
class LalbarancliCorunya extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lalbarancli_corunya';
    protected $primaryKey = 'idlalbarancli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idarticulo', 'idmovalm', 'idalbarancli', 'idusuariomod', 'pcosto',
        'precio', 'not', 'unidades', 'not', 'dto',
        'not', 'iva', 'not', 'recargo', 'not',
        'precioorigen', 'idoferta', 'idalmacen', 'idtipomedida', 'observaciones',
        'unid', 'idlote', 'seclote', 'idlpedidocli', 'notapieza',
        'notageneral', 'idlalbarancliorig', 'idtipodescuento', 'total_bi', 'total_con_impuestos',
        'origen_kardex', 'idbono_promocion', 'guiapertenencia', 'fguiapertenencia', 'narma',
        'ngrupo_segundamano', 'total_neto', 'numero_serie', 'numticket', 'genera_puntos',
        'parte_exenta', 'not', 'tarifa_genera_puntos', 'idempleado_gfitters',
    ];

    protected $casts = [
        'fguiapertenencia' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lalbarancli
     * ✅ Usa PK_LALBARANCLI_CORUNYA (indexado)
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
     * ⚠️  SIN ÍNDICE en IDALBARANCLI
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
     * ⚠️  SIN ÍNDICE en IDLPEDIDOCLI
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
     * ⚠️  SIN ÍNDICE en IDBONO_PROMOCION
     */
    public function bonoPromocion()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\BonoPromocion::class, 'IDBONO_PROMOCION', 'IDBONO_PROMOCION');
    }

}
