<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LIMPORTACION_ARTICULO_ACT
 *
 * ÍNDICES DISPONIBLES:
 * PK_LIMPORTACION_ARTICULO_ACT (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLIMPORTACION_ARTICULO_ACT
 *
 */
class LimportacionArticuloAct extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'limportacion_articulo_act';
    protected $primaryKey = 'idlimportacion_articulo_act';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idimportacion_articulo', 'idusuariocre', 'idusuariomod', 'idusuariobaj', 'estado',
        'seleccionado', 'fprocesado', 'idartiprov', 'preciorecomendadoprov_coniva', 'pcosto',
        'dto1', 'dto2', 'actualizar_coste', 'codigopro', 'descripcionpro',
        'pvp', 'pvp_portugal', 'tarifa_calculada', 'actualizar_pvp', 'ean13',
        'upc', 'peso',
    ];

    protected $casts = [
        'fprocesado' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: LimportacionArticuloAct
     * ✅ Usa PK_LIMPORTACION_ARTICULO_ACT (indexado)
     */
    public function limportacionArticuloAct()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\LimportacionArticuloAct::class, 'IDLIMPORTACION_ARTICULO_ACT', 'IDLIMPORTACION_ARTICULO_ACT');
    }

    /**
     * Relación: ImportacionArticulo
     * ⚠️  SIN ÍNDICE en IDIMPORTACION_ARTICULO
     */
    public function importacionArticulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\ImportacionArticulo::class, 'IDIMPORTACION_ARTICULO', 'IDIMPORTACION_ARTICULO');
    }

    /**
     * Relación: Artiprov
     * ⚠️  SIN ÍNDICE en IDARTIPROV
     */
    public function artiprov()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Artiprov::class, 'IDARTIPROV', 'IDARTIPROV');
    }

}
