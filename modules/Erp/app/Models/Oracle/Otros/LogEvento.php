<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LOG_EVENTO
 *
 * ÍNDICES DISPONIBLES:
 * PK_LOG_EVENTO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLOG_EVENTO
 *
 */
class LogEvento extends Model
{
    protected $connection = 'oracle';
    protected $table = 'log_evento';
    protected $primaryKey = 'idlog_evento';
    public $timestamps = false;

    protected $fillable = [
        'fecha', 'idevento', 'identificador', 'remitente', 'destintatario',
        'asunto', 'cuerpo', 'cuerpoclob',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: LogEvento
     * ✅ Usa PK_LOG_EVENTO (indexado)
     */
    public function logEvento()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\LogEvento::class, 'IDLOG_EVENTO', 'IDLOG_EVENTO');
    }

    /**
     * Relación: Evento
     * ⚠️  SIN ÍNDICE en IDEVENTO
     */
    public function evento()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Evento::class, 'IDEVENTO', 'IDEVENTO');
    }

}
