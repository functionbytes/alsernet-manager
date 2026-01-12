<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla TARIFA_CABECERA
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_TARIFA_CABECERA_FFIN (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: FFIN
 *
 * ✅ IDX_TARIFA_CABECERA_FINICIO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: FINICIO
 *
 * ✅ IDX_TARIFA_CABECERA_IDALMACEN (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDALMACEN
 *
 * ✅ IDX_TARIFA_CABECERA_IDARTICULO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTICULO
 *
 * ✅ IDX_TARIFA_CABECERA_IDIMPPAIS_ (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDIMPPAIS_FECHA
 *
 * ✅ IDX_TARIFA_CABECERA_IDREGPAIS (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDREGPAIS
 *
 * ✅ IDX_TARIFA_CABECERA_IDTARIFA_C (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTARIFA_CABECERA_TCALCULO
 *
 * PK_TARIFACABECERA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTARIFA_CABECERA
 *
 */
class TarifaCabecera extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'tarifa_cabecera';
    protected $primaryKey = 'idtarifa_cabecera_tcalculo';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idtarifa_cabecera', 'idusuariocre', 'idusuariomod', 'idusuariobaj', 'estado',
        'idarticulo', 'idalmacen', 'idregpais', 'idimppais_fecha', 'porc_iva',
        'not', 'tarifa_base', 'tarifa_calculada', 'importe_exento', 'not',
        'finicio', 'ffin', 'idttarifa',
    ];

    protected $casts = [
        'finicio' => 'datetime',
        'ffin' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: TarifaCabecera
     * ✅ Usa PK_TARIFACABECERA (indexado)
     */
    public function tarifaCabecera()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\TarifaCabecera::class, 'IDTARIFA_CABECERA', 'IDTARIFA_CABECERA');
    }

    /**
     * Relación: Articulo
     * ✅ Usa IDX_TARIFA_CABECERA_IDARTICULO (indexado)
     */
    public function articulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Articulo\Articulo::class, 'IDARTICULO', 'IDARTICULO');
    }

    /**
     * Relación: Almacen
     * ✅ Usa IDX_TARIFA_CABECERA_IDALMACEN (indexado)
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

    /**
     * Relación: Regpais
     * ✅ Usa IDX_TARIFA_CABECERA_IDREGPAIS (indexado)
     */
    public function regpais()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Regpais::class, 'IDREGPAIS', 'IDREGPAIS');
    }

    /**
     * Relación: ImppaisFecha
     * ✅ Usa IDX_TARIFA_CABECERA_IDIMPPAIS_ (indexado)
     */
    public function imppaisFecha()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\ImppaisFecha::class, 'IDIMPPAIS_FECHA', 'IDIMPPAIS_FECHA');
    }

    /**
     * Relación: TarifaCabeceraTcalculo
     * ✅ Usa IDX_TARIFA_CABECERA_IDTARIFA_C (indexado)
     */
    public function tarifaCabeceraTcalculo()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\TarifaCabeceraTcalculo::class, 'IDTARIFA_CABECERA_TCALCULO', 'IDTARIFA_CABECERA_TCALCULO');
    }

    /**
     * Relación: Ttarifa
     * ⚠️  SIN ÍNDICE en IDTTARIFA
     */
    public function ttarifa()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Ttarifa::class, 'IDTTARIFA', 'IDTTARIFA');
    }

}
