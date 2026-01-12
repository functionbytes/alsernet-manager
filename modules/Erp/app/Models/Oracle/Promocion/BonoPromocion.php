<?php

namespace Modules\Erp\Models\Oracle\Promocion;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Catalogo\Catalogo;

/**
 * Modelo para la tabla BONO_PROMOCION
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_BONO_PROMOCION_IDTBONO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTBONO_PROMOCION
 *
 * ✅ IDX_BONO_PROMO_IDALBARAN (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDALBARANCLI
 *
 * ✅ IDX_BONO_PROMO_IDALMACEN (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDALMACEN_CREACION
 *
 * ✅ IDX_BONO_PROMO_IDCLIENTE (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTE
 *
 * ✅ IDX_BONO_PROMO_IDLIQUIDACION (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLIQUIDACIONBONO
 *
 * ✅ IDX_BONO_PROMO_IDLPROMOCION (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPROMOCION
 *
 * PK_BONO_PROMOCION (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDBONO_PROMOCION
 *
 */
class BonoPromocion extends Model
{
    protected $connection = 'oracle';
    protected $table = 'bono_promocion';
    protected $primaryKey = 'idbono_promocion';
    public $timestamps = false;

    protected $fillable = [
        'idalbarancli', 'idlpromocion', 'fvalidez_desde', 'fvalidez_hasta', 'estado',
        'importe', 'not', 'idalmacen_creacion', 'tipo', 'estado_envio',
        'enviado_sms', 'enviado_mail', 'enviado_carta', 'idliquidacionbono', 'idcliente',
        'fecha', 'fconsumo', 'fenvio', 'importeminimoventa', 'bonoimpreso',
        'idtbono_promocion', 'idcatalogo_consumo', 'tipotarjeta', 'idtipotarjetaregalo',
    ];

    protected $casts = [
        'fvalidez_desde' => 'datetime',
        'fvalidez_hasta' => 'datetime',
        'fecha' => 'datetime',
        'fconsumo' => 'datetime',
        'fenvio' => 'datetime',
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con Catalogo
     */
    public function catalogo_consumo()
    {
        return $this->belongsTo(Catalogo::class, 'idcatalogo_consumo', 'idcatalogo');
    }

    /**
     * Relación con TbonoPromocion
     */
    public function tbono_promocion()
    {
        return $this->belongsTo(TbonoPromocion::class, 'idtbono_promocion', 'idtbono_promocion');
    }


    /**
     * Relación: CatalogoConsumo
     * ⚠️  SIN ÍNDICE en IDCATALOGO_CONSUMO
     */
    public function catalogoConsumo()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\Catalogo::class, 'IDCATALOGO_CONSUMO', 'IDCATALOGO');
    }

    /**
     * Relación: TbonoPromocion
     * ✅ Usa IDX_BONO_PROMOCION_IDTBONO (indexado)
     */
    public function tbonoPromocion()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\TbonoPromocion::class, 'IDTBONO_PROMOCION', 'IDTBONO_PROMOCION');
    }


    /**
     * Relación: BonoPromocion
     * ✅ Usa PK_BONO_PROMOCION (indexado)
     */
    public function bonoPromocion()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\BonoPromocion::class, 'IDBONO_PROMOCION', 'IDBONO_PROMOCION');
    }

    /**
     * Relación: Albarancli
     * ✅ Usa IDX_BONO_PROMO_IDALBARAN (indexado)
     */
    public function albarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbarancliCapthaya::class, 'IDALBARANCLI', 'IDALBARANCLI');
    }

    /**
     * Relación: Lpromocion
     * ✅ Usa IDX_BONO_PROMO_IDLPROMOCION (indexado)
     */
    public function lpromocion()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Lpromocion::class, 'IDLPROMOCION', 'IDLPROMOCION');
    }

    /**
     * Relación: Liquidacionbono
     * ✅ Usa IDX_BONO_PROMO_IDLIQUIDACION (indexado)
     */
    public function liquidacionbono()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Liquidacionbono::class, 'IDLIQUIDACIONBONO', 'IDLIQUIDACIONBONO');
    }

    /**
     * Relación: Cliente
     * ✅ Usa IDX_BONO_PROMO_IDCLIENTE (indexado)
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
    }

}
