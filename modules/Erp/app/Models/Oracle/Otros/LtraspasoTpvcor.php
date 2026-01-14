<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LTRASPASO_TPVCOR
 *
 * ÍNDICES DISPONIBLES:
 * PK_LTRASPASO_TPVCOR (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLTRASPASO
 *
 */
class LtraspasoTpvcor extends Model
{
    protected $connection = 'oracle';
    protected $table = 'ltraspaso_tpvcor';
    protected $primaryKey = 'idltraspaso';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idtraspaso', 'idmovalm', 'idarticulo', 'unidades', 'not',
        'idusuariomod', 'idlfacturacli', 'idlpedidodel', 'unidades_enviadas', 'observaciones',
        'numero_serie',
    ];
}
