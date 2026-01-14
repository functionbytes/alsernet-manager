<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LTRASPASO_CAPTHAYA
 *
 * ÍNDICES DISPONIBLES:
 * PK_LTRASPASO_CAPTHAYA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLTRASPASO
 *
 */
class LtraspasoCapthaya extends Model
{
    protected $connection = 'oracle';
    protected $table = 'ltraspaso_capthaya';
    protected $primaryKey = 'idltraspaso';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idtraspaso', 'idmovalm', 'idarticulo', 'unidades', 'not',
        'idusuariomod', 'idlfacturacli', 'idlpedidodel', 'unidades_enviadas', 'observaciones',
        'numero_serie',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Ltraspaso
     * ✅ Usa PK_LTRASPASO_CAPTHAYA (indexado)
     */
    public function ltraspaso()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\LtraspasoCapthaya::class, 'IDLTRASPASO', 'IDLTRASPASO');
    }

    /**
     * Relación: Traspaso
     * ⚠️  SIN ÍNDICE en IDTRASPASO
     */
    public function traspaso()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\TraspasoCapthaya::class, 'IDTRASPASO', 'IDTRASPASO');
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
     * Relación: Lfacturacli
     * ⚠️  SIN ÍNDICE en IDLFACTURACLI
     */
    public function lfacturacli()
    {
        return $this->belongsTo(\App\Models\Oracle\Factura\LfacturacliCentral::class, 'IDLFACTURACLI', 'IDLFACTURACLI');
    }

}
