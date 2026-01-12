<?php

namespace Modules\Erp\Models\Oracle\Proveedor;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla LPROPUESTAPRO_MINIMO
 *
 * ÍNDICES DISPONIBLES:
 * PK_LPROPUESTAPRO_MINIMO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPROPUESTAPRO_MINIMO
 *
 */
class LpropuestaproMinimo extends Model
{
    protected $connection = 'oracle';
    protected $table = 'lpropuestapro_minimo';
    protected $primaryKey = 'idlpropuestapro_minimo';
    public $timestamps = false;

    protected $fillable = [
        'idlpropuestapro', 'idalmacen', 'minimo_original', 'maximo_original', 'recomendado_original',
        'minimo', 'maximo', 'recomendado',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: LpropuestaproMinimo
     * ✅ Usa PK_LPROPUESTAPRO_MINIMO (indexado)
     */
    public function lpropuestaproMinimo()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\LpropuestaproMinimo::class, 'IDLPROPUESTAPRO_MINIMO', 'IDLPROPUESTAPRO_MINIMO');
    }

    /**
     * Relación: Lpropuestapro
     * ⚠️  SIN ÍNDICE en IDLPROPUESTAPRO
     */
    public function lpropuestapro()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Lpropuestapro::class, 'IDLPROPUESTAPRO', 'IDLPROPUESTAPRO');
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
