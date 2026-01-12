<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CONDICIONPAGO
 *
 * ÍNDICES DISPONIBLES:
 * PK_IDCONDICIONPAGO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCONDICIONPAGO
 *
 */
class Condicionpago extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'condicionpago';
    protected $primaryKey = 'idcondicionpago';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idusuariocre', 'idusuariomod', 'idusuariobaj', 'borrado', 'descripcion',
        'alias', 'nplazos', 'ndias', 'diasprimero',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Condicionpago
     * ✅ Usa PK_IDCONDICIONPAGO (indexado)
     */
    public function condicionpago()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Condicionpago::class, 'IDCONDICIONPAGO', 'IDCONDICIONPAGO');
    }

}
