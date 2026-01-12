<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla NOTIFICACION_CENTRAL
 *
 * ÍNDICES DISPONIBLES:
 * PK_NOTIFICACION_CENTRAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDNOTIFICACION_CENTRAL
 *
 */
class Notificacion extends Model
{
    protected $connection = 'oracle';
    protected $table = 'notificacion_central';
    protected $primaryKey = 'idnotificacion_central';
    public $timestamps = false;

    protected $fillable = [
        'tipo', 'fecha', 'parametros', 'origen',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: NotificacionCentral
     * ✅ Usa PK_NOTIFICACION_CENTRAL (indexado)
     */
    public function notificacionCentral()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Notificacion::class, 'IDNOTIFICACION_CENTRAL', 'IDNOTIFICACION_CENTRAL');
    }

}
