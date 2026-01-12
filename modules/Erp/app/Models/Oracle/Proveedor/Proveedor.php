<?php

namespace Modules\Erp\Models\Oracle\Proveedor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Erp\Models\Oracle\Configuracion\Pais;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla PROVEEDOR
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_PROVEEDOR_IDPAIS (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPAIS
 *
 * PK_PROVEEDOR (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPROVEEDOR
 *
 */
class Proveedor extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'proveedor';
    protected $primaryKey = 'idproveedor';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idtipoprov', 'idbanco', 'nombre', 'cif', 'percontacto',
        'telefono1', 'telefono2', 'fax', 'observaciones', 'web',
        'email', 'portes', 'codcliente', 'estado', 'calle',
        'num', 'localidad', 'cp', 'provincia', 'pais',
        'sucursal_', 'dc_', 'ncuenta_', 'idregfiscal', 'dto',
        'idusuariomod', 'idsubcuenta', 'numart', 'acreedor', 'idmoneda',
        'tposervicio', 'visible', 'nsubcuenta', 'observacionportes', 'iban',
        'idpais', 'dias_servir_parcial', 'porcentaje_servir_parcial',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con Pais
     */
    public function pais()
    {
        return $this->belongsTo(Pais::class, 'idpais', 'idpais');
    }


    /**
     * Relación: Proveedor
     * ✅ Usa PK_PROVEEDOR (indexado)
     */
    public function proveedor()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Proveedor::class, 'IDPROVEEDOR', 'IDPROVEEDOR');
    }

    /**
     * Relación: Tipoprov
     * ⚠️  SIN ÍNDICE en IDTIPOPROV
     */
    public function tipoprov()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Tipoprov::class, 'IDTIPOPROV', 'IDTIPOPROV');
    }

    /**
     * Relación: Banco
     * ⚠️  SIN ÍNDICE en IDBANCO
     */
    public function banco()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Banco::class, 'IDBANCO', 'IDBANCO');
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
     * Relación: Subcuenta
     * ⚠️  SIN ÍNDICE en IDSUBCUENTA
     */
    public function subcuenta()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Subcuenta::class, 'IDSUBCUENTA', 'IDSUBCUENTA');
    }

    /**
     * Relación: Moneda
     * ⚠️  SIN ÍNDICE en IDMONEDA
     */
    public function moneda()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Moneda::class, 'IDMONEDA', 'IDMONEDA');
    }

    /**
     * Relación inversa: SupplierErpProviders (sincronización Supplier)
     */
    public function supplierErpProviders(): HasMany
    {
        return $this->hasMany(\Modules\Supplier\Entities\SupplierErpProvider::class, 'erp_provider_id', 'idproveedor');
    }

}
