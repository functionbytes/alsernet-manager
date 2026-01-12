<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla TRASPASO_DDLEON
 *
 * ÍNDICES DISPONIBLES:
 * PK_TRASPASO_DDLEON (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTRASPASO
 *
 */
class TraspasoDdleon extends Model
{
    protected $connection = 'oracle';
    protected $table = 'traspaso_ddleon';
    protected $primaryKey = 'idtraspaso';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';

    protected $fillable = [
        'idalmacen', 'alm_idalmacen', 'alm_idalmacen2', 'ftraspaso', 'observaciones',
        'estado', 'idtraspasoorig', 'tipo', 'idusuariomod', 'idserietraspaso',
        'ntraspaso', 'idempleado', 'estpowerpick',
    ];

    protected $casts = [
        'ftraspaso' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Traspaso
     * ✅ Usa PK_TRASPASO_DDLEON (indexado)
     */
    public function traspaso()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\TraspasoCapthaya::class, 'IDTRASPASO', 'IDTRASPASO');
    }

    /**
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

}
