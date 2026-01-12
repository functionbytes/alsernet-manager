<?php

namespace Modules\Erp\Models\Oracle\Albaran;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LALBARANPRO_TPVCOR
 *
 * ÍNDICES DISPONIBLES:
 * PK_LALBARANPRO_TPVCOR (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLALBARANPRO
 *
 */
class Lalbaranpro extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lalbaranpro_tpvcor';
    protected $primaryKey = 'idlalbaranpro';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idmovalm', 'idlpedidopro', 'idalbaranpro', 'idarticulo', 'idusuariomod',
        'unidades', 'not', 'unidalb', 'precio', 'not',
        'dto', 'not', 'iva', 'not', 'recargo',
        'not', 'idtipomedida', 'preciocosto', 'unid', 'notapieza',
        'dto2', 'idlalbaranclireparacion', 'preciomonedaoriginal', 'ubicacion', 'estaubicado',
        'idlalbaranpro_central', 'idalbaranpro_central', 'idalmacen_creacion', 'numero_serie',
    ];
}
