<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla VALE
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_VALE_IDCLIENTE (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTE
 *
 * PK_VALE (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDVALE
 *
 */
class Vale extends Model
{
    protected $connection = 'oracle';
    protected $table = 'vale';
    protected $primaryKey = 'idvale_anterior';
    public $timestamps = false;

    protected $fillable = [
        'idvale', 'importe', 'fanulacion', 'estado', 'idalmacen',
        'fvalidez', 'tipo', 'idcliente', 'observaciones', 'tiene_codigo_comprobacion',
        'idvale_original',
    ];

    protected $casts = [
        'fanulacion' => 'datetime',
        'fvalidez' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Vale
     * ✅ Usa PK_VALE (indexado)
     */
    public function vale()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Vale::class, 'IDVALE', 'IDVALE');
    }

    /**
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

    /**
     * Relación: Cliente
     * ✅ Usa IDX_VALE_IDCLIENTE (indexado)
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
    }

}
