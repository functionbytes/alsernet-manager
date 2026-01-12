<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla CAMBIOTABLAXDESTINO
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_CAMBIOTABLAXDEST_ESTADO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ESTADO
 *
 * ✅ IDX_CAMBIOTABLAXDEST_IDCAMBTAB (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCAMBIOTABLA
 *
 * PK_CAMBIOTABLAXDESTINO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCAMBIOTABLAXDESTINO
 *
 */
class Cambiotablaxdestino extends Model
{
    protected $connection = 'oracle';
    protected $table = 'cambiotablaxdestino';
    protected $primaryKey = 'idcambiotablaxdestino';
    public $timestamps = false;

    protected $fillable = [
        'idcambiotabla', 'iddestinocambio', 'fmodifiacion', 'estado',
    ];

    protected $casts = [
        'fmodifiacion' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Cambiotablaxdestino
     * ✅ Usa PK_CAMBIOTABLAXDESTINO (indexado)
     */
    public function cambiotablaxdestino()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Cambiotablaxdestino::class, 'IDCAMBIOTABLAXDESTINO', 'IDCAMBIOTABLAXDESTINO');
    }

    /**
     * Relación: Cambiotabla
     * ✅ Usa IDX_CAMBIOTABLAXDEST_IDCAMBTAB (indexado)
     */
    public function cambiotabla()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Cambiotabla::class, 'IDCAMBIOTABLA', 'IDCAMBIOTABLA');
    }

    /**
     * Relación: Destinocambio
     * ⚠️  SIN ÍNDICE en IDDESTINOCAMBIO
     */
    public function destinocambio()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Destinocambio::class, 'IDDESTINOCAMBIO', 'IDDESTINOCAMBIO');
    }

}
