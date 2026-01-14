<?php

namespace Modules\Erp\Models\Oracle\Factura;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LFACTURAPRO
 *
 * ÍNDICES DISPONIBLES:
 * PK_LFACTURAPRO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLFACTURAPRO
 *
 */
class Lfacturapro extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lfacturapro';
    protected $primaryKey = 'idlfacturapro';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idfacturapro', 'idlalbaranpro', 'codigo', 'descripcion', 'unidades',
        'not', 'precio', 'not', 'iva', 'not',
        'recargo', 'not', 'dto', 'not', 'idusuariomod',
        'idtipomedida', 'unid', 'dto2', 'idcatalogo', 'idalmacen',
        'idlalbaranpro_central',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lfacturapro
     * ✅ Usa PK_LFACTURAPRO (indexado)
     */
    public function lfacturapro()
    {
        return $this->belongsTo(\App\Models\Oracle\Factura\Lfacturapro::class, 'IDLFACTURAPRO', 'IDLFACTURAPRO');
    }

    /**
     * Relación: Facturapro
     * ⚠️  SIN ÍNDICE en IDFACTURAPRO
     */
    public function facturapro()
    {
        return $this->belongsTo(\App\Models\Oracle\Factura\Facturapro::class, 'IDFACTURAPRO', 'IDFACTURAPRO');
    }

    /**
     * Relación: Lalbaranpro
     * ⚠️  SIN ÍNDICE en IDLALBARANPRO
     */
    public function lalbaranpro()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\LalbaranproCapthaya::class, 'IDLALBARANPRO', 'IDLALBARANPRO');
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
     * Relación: Catalogo
     * ⚠️  SIN ÍNDICE en IDCATALOGO
     */
    public function catalogo()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\Catalogo::class, 'IDCATALOGO', 'IDCATALOGO');
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
     * Relación: LalbaranproCentral
     * ⚠️  SIN ÍNDICE en IDLALBARANPRO_CENTRAL
     */
    public function lalbaranproCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\LalbaranproCentral::class, 'IDLALBARANPRO_CENTRAL', 'IDLALBARANPRO_CENTRAL');
    }

}
