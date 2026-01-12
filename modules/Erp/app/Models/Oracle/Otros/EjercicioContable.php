<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla EJERCICIO_CONTABLE_CENT
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_EJ_CONTABLE_CENT_DIARIO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDDIARIODEFECTO
 *
 * PK_EJERCICIO_CONTABLE_CENT (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDEJERCICIO_CONTABLE
 *
 */
class EjercicioContable extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'ejercicio_contable_cent';
    protected $primaryKey = 'idejercicio_contable';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idempresa', 'idusuariocre', 'idusuariomod', 'idusuariobaj', 'estado',
        'descripcion', 'finicio', 'ffin', 'iddiariodefecto', 'fecha_bloqueo',
        'codenlace',
    ];

    protected $casts = [
        'finicio' => 'datetime',
        'ffin' => 'datetime',
        'fecha_bloqueo' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: EjercicioContable
     * ✅ Usa PK_EJERCICIO_CONTABLE_CENT (indexado)
     */
    public function ejercicioContable()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\EjercicioContable::class, 'IDEJERCICIO_CONTABLE', 'IDEJERCICIO_CONTABLE');
    }

    /**
     * Relación: Empresa
     * ⚠️  SIN ÍNDICE en IDEMPRESA
     */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Empresa::class, 'IDEMPRESA', 'IDEMPRESA');
    }

}
