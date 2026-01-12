<?php

namespace Modules\Erp\Models\Oracle\Factura;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla FACTURACLI_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_FACTURACLI_IDASIENTO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDASIENTO
 *
 * ✅ INDX_FACTURACLI_IDFACTURACLI (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDFACTURACLI
 *
 */
class FacturacliCentral extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'facturacli_central';
    protected $primaryKey = 'idfacturacli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcliente', 'iddeuda', 'idregfiscal', 'idserie', 'nfactura',
        'anno', 'nombre', 'cif', 'calle', 'numero',
        'localidad', 'cp', 'provincia', 'pais', 'ffactura',
        'idusuariomod', 'nombre_emp', 'cif_emp', 'calle_emp', 'numero_emp',
        'localidad_emp', 'cp_emp', 'provincia_emp', 'pais_emp', 'dto',
        'not', 'idempleado', 'observaciones', 'dto2', 'idasiento',
        'idpais', 'tipo', 'idformapago', 'estado', 'idsubcta_cli',
        'pasar_a_conta', 'idcatalogo', 'idsubcta_venta', 'idregpais', 'idalmacen',
        'oficina_contable', 'organo_gestor', 'unidad_tramitadora', 'idfacturacli_rectificada', 'simplificada',
        'organo_proponente',
    ];

    protected $casts = [
        'ffactura' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Facturacli
     * ✅ Usa INDX_FACTURACLI_IDFACTURACLI (indexado)
     */
    public function facturacli()
    {
        return $this->belongsTo(\App\Models\Oracle\Factura\FacturacliCentral::class, 'IDFACTURACLI', 'IDFACTURACLI');
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
     * Relación: Serie
     * ⚠️  SIN ÍNDICE en IDSERIE
     */
    public function serie()
    {
        return $this->belongsTo(\App\Models\Oracle\Serie\Serie::class, 'IDSERIE', 'IDSERIE');
    }

    /**
     * Relación: Asiento
     * ✅ Usa IDX_FACTURACLI_IDASIENTO (indexado)
     */
    public function asiento()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\AsientoCent::class, 'IDASIENTO', 'IDASIENTO');
    }

    /**
     * Relación: Pais
     * ⚠️  SIN ÍNDICE en IDPAIS
     */
    public function pais()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Pais::class, 'IDPAIS', 'IDPAIS');
    }

    /**
     * Relación: Formapago
     * ⚠️  SIN ÍNDICE en IDFORMAPAGO
     */
    public function formapago()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\Formapago::class, 'IDFORMAPAGO', 'IDFORMAPAGO');
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
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

}
