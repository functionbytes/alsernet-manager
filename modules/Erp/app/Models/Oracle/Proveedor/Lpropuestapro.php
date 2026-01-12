<?php

namespace Modules\Erp\Models\Oracle\Proveedor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LPROPUESTAPRO
 *
 * ÍNDICES DISPONIBLES:
 * PK_LPROPUESTAPRO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPROPUESTAPRO
 *
 */
class Lpropuestapro extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lpropuestapro';
    protected $primaryKey = 'idlpropuestapro';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idpropuestapro', 'codigopro', 'descripcion', 'observaciones', 'unidades',
        'not', 'unidadespedir', 'not', 'idarticulo', 'estado',
        'idusuariocre', 'idusuariomod', 'idusuariobaj', 'pcosto', 'dto1',
        'dto2', 'precio', 'idgrupo_cl', 'idcatalogo', 'idimpuesto',
        'codbar', 'idlpedidocli', 'preciorecomendadoprov', 'upc', 'idmodelo',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lpropuestapro
     * ✅ Usa PK_LPROPUESTAPRO (indexado)
     */
    public function lpropuestapro()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Lpropuestapro::class, 'IDLPROPUESTAPRO', 'IDLPROPUESTAPRO');
    }

    /**
     * Relación: Propuestapro
     * ⚠️  SIN ÍNDICE en IDPROPUESTAPRO
     */
    public function propuestapro()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Propuestapro::class, 'IDPROPUESTAPRO', 'IDPROPUESTAPRO');
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
     * Relación: GrupoCl
     * ⚠️  SIN ÍNDICE en IDGRUPO_CL
     */
    public function grupoCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\GrupoCl::class, 'IDGRUPO_CL', 'IDGRUPO_CL');
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
     * Relación: Impuesto
     * ⚠️  SIN ÍNDICE en IDIMPUESTO
     */
    public function impuesto()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Impuesto::class, 'IDIMPUESTO', 'IDIMPUESTO');
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
     * Relación: Modelo
     * ⚠️  SIN ÍNDICE en IDMODELO
     */
    public function modelo()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\Modelo::class, 'IDMODELO', 'IDMODELO');
    }

}
