<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla IMPPAIS_FECHA
 *
 * ÍNDICES DISPONIBLES:
 * PK_IMPPAIS_FECHA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDIMPPAIS_FECHA
 *
 */
class ImppaisFecha extends Model
{
    protected $connection = 'oracle';
    protected $table = 'imppais_fecha';
    protected $primaryKey = 'idimppais_fecha';
    public $timestamps = false;

    protected $fillable = [
        'idimpuesto', 'idregpais', 'valoriva', 'not', 'recargo',
        'not', 'fhasta', 'codigo', 'descripcion',
    ];

    protected $casts = [
        'fhasta' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: ImppaisFecha
     * ✅ Usa PK_IMPPAIS_FECHA (indexado)
     */
    public function imppaisFecha()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\ImppaisFecha::class, 'IDIMPPAIS_FECHA', 'IDIMPPAIS_FECHA');
    }

    /**
     * Relación: Impuesto
     * ⚠️  SIN ÍNDICE en IDIMPUESTO
     */
    public function impuesto()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Impuesto::class, 'IDIMPUESTO', 'IDIMPUESTO');
    }

    /**
     * Relación: Regpais
     * ⚠️  SIN ÍNDICE en IDREGPAIS
     */
    public function regpais()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Regpais::class, 'IDREGPAIS', 'IDREGPAIS');
    }

}
