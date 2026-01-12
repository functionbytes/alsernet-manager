<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla FRCAMPO
 *
 * ÍNDICES DISPONIBLES:
 * PK_FRCAMPO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: CAM_IDCAMPO, IDCAMPO
 *
 */
class Frcampo extends Model
{
    protected $connection = 'oracle';
    protected $table = 'frcampo';
    protected $primaryKey = 'cam_idcampo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'idcampo',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Campo
     * ⚠️  SIN ÍNDICE en IDCAMPO
     */
    public function campo()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Campo::class, 'IDCAMPO', 'IDCAMPO');
    }

}
