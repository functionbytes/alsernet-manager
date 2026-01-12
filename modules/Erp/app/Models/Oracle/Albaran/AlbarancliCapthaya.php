<?php

namespace Modules\Erp\Models\Oracle\Albaran;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla ALBARANCLI_CAPTHAYA
 *
 * ÍNDICES DISPONIBLES:
 * ✅ INDX_ALBARANCLI_CAP_IDFACLI (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDFACTURACLI
 *
 * PK_ALBARANCLI_CAPTHAYA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDALBARANCLI
 *
 */
class AlbarancliCapthaya extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'albarancli_capthaya';
    protected $primaryKey = 'idalbarancli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcliente', 'idregfiscal', 'idalmacen', 'idseriealbarancli', 'idusuariomod',
        'falbaran', 'nalbarancli', 'idcierre', 'idempleado', 'estado',
        'tipo', 'tentrada', 'observaciones', 'idtipoalbarancli', 'clientetelefono',
        'idenvio', 'nroserie', 'solicita_factura', 'idcatalogo', 'idalbarancli_orig',
        'idregpais', 'idsubc_cli', 'puntosfideliz', 'idfacturacli', 'es_compromiso_alvarez',
        'nfactura_simplificada', 'fenvio_opinion', 'email',
    ];

    protected $casts = [
        'falbaran' => 'datetime',
        'fenvio_opinion' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Albarancli
     * ✅ Usa PK_ALBARANCLI_CAPTHAYA (indexado)
     */
    public function albarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbarancliCapthaya::class, 'IDALBARANCLI', 'IDALBARANCLI');
    }

    /**
     * Relación: Cliente
     * ⚠️  SIN ÍNDICE en IDCLIENTE
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
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
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

    /**
     * Relación: Seriealbarancli
     * ⚠️  SIN ÍNDICE en IDSERIEALBARANCLI
     */
    public function seriealbarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\SeriealbarancliCapthaya::class, 'IDSERIEALBARANCLI', 'IDSERIEALBARANCLI');
    }

    /**
     * Relación: Cierre
     * ⚠️  SIN ÍNDICE en IDCIERRE
     */
    public function cierre()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Cierre::class, 'IDCIERRE', 'IDCIERRE');
    }

    /**
     * Relación: Tipoalbarancli
     * ⚠️  SIN ÍNDICE en IDTIPOALBARANCLI
     */
    public function tipoalbarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\Tipoalbarancli::class, 'IDTIPOALBARANCLI', 'IDTIPOALBARANCLI');
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
     * Relación: Regpais
     * ⚠️  SIN ÍNDICE en IDREGPAIS
     */
    public function regpais()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Regpais::class, 'IDREGPAIS', 'IDREGPAIS');
    }

    /**
     * Relación: Facturacli
     * ✅ Usa INDX_ALBARANCLI_CAP_IDFACLI (indexado)
     */
    public function facturacli()
    {
        return $this->belongsTo(\App\Models\Oracle\Factura\FacturacliCentral::class, 'IDFACTURACLI', 'IDFACTURACLI');
    }

}
