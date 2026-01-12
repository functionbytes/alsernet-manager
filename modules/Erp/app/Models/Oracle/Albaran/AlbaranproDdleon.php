<?php

namespace Modules\Erp\Models\Oracle\Albaran;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla ALBARANPRO_DDLEON
 *
 * ÍNDICES DISPONIBLES:
 * PK_ALBARANPRO_DDLEON (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDALBARANPRO
 *
 */
class AlbaranproDdleon extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'albaranpro_ddleon';
    protected $primaryKey = 'idalbaranpro';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idpedidopro', 'idproveedor', 'idalmacen', 'idalbarancli', 'idregfiscal',
        'idusuariomod', 'fentrada', 'dto', 'not', 'nalbaranpro',
        'idempleado', 'idseriealbaranpro', 'portes', 'idusuariocre', 'idusuariobaj',
        'nrefalbaranpro', 'tipo', 'idenvio', 'idconversionmoneda', 'idcatalogo',
        'estpowerpick', 'estaubicado', 'observaciones', 'facturadoprovisorio', 'fentrada_real',
        'idalbaranpro_central', 'idalmacen_creacion',
    ];

    protected $casts = [
        'fentrada' => 'datetime',
        'fentrada_real' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Albaranpro
     * ✅ Usa PK_ALBARANPRO_DDLEON (indexado)
     */
    public function albaranpro()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbaranproCapthaya::class, 'IDALBARANPRO', 'IDALBARANPRO');
    }

    /**
     * Relación: Pedidopro
     * ⚠️  SIN ÍNDICE en IDPEDIDOPRO
     */
    public function pedidopro()
    {
        return $this->belongsTo(\App\Models\Oracle\Pedido\PedidoproCapthaya::class, 'IDPEDIDOPRO', 'IDPEDIDOPRO');
    }

    /**
     * Relación: Proveedor
     * ⚠️  SIN ÍNDICE en IDPROVEEDOR
     */
    public function proveedor()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Proveedor::class, 'IDPROVEEDOR', 'IDPROVEEDOR');
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
     * Relación: Albarancli
     * ⚠️  SIN ÍNDICE en IDALBARANCLI
     */
    public function albarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbarancliCapthaya::class, 'IDALBARANCLI', 'IDALBARANCLI');
    }

    /**
     * Relación: Regfiscal
     * ⚠️  SIN ÍNDICE en IDREGFISCAL
     */
    public function regfiscal()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Regfiscal::class, 'IDREGFISCAL', 'IDREGFISCAL');
    }

    /**
     * Relación: Conversionmoneda
     * ⚠️  SIN ÍNDICE en IDCONVERSIONMONEDA
     */
    public function conversionmoneda()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Conversionmoneda::class, 'IDCONVERSIONMONEDA', 'IDCONVERSIONMONEDA');
    }

    /**
     * Relación: Catalogo
     * ⚠️  SIN ÍNDICE en IDCATALOGO
     */
    public function catalogo()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\Catalogo::class, 'IDCATALOGO', 'IDCATALOGO');
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
