<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla PUNTOFIDELIZACION
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_PUNTOFID_IDCLIENTE (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCLIENTE
 *
 * PK_PUNTOFIDELIZACION (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPUNTOFIDELIZACION
 *
 */
class Puntofidelizacion extends Model
{
    protected $connection = 'oracle';
    protected $table = 'puntofidelizacion';
    protected $primaryKey = 'idpuntofidelizacion';
    public $timestamps = false;

    protected $fillable = [
        'idtarjeta', 'puntos', 'fecha', 'idalmacen', 'idliquidacion',
        'idalbarancli', 'idcliente', 'estado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Puntofidelizacion
     * ✅ Usa PK_PUNTOFIDELIZACION (indexado)
     */
    public function puntofidelizacion()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Puntofidelizacion::class, 'IDPUNTOFIDELIZACION', 'IDPUNTOFIDELIZACION');
    }

    /**
     * Relación: Tarjeta
     * ⚠️  SIN ÍNDICE en IDTARJETA
     */
    public function tarjeta()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Tarjetas::class, 'IDTARJETA', 'IDTARJETA');
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
     * Relación: Albarancli
     * ⚠️  SIN ÍNDICE en IDALBARANCLI
     */
    public function albarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbarancliCapthaya::class, 'IDALBARANCLI', 'IDALBARANCLI');
    }

    /**
     * Relación: Cliente
     * ✅ Usa IDX_PUNTOFID_IDCLIENTE (indexado)
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Oracle\Cliente\Cliente::class, 'IDCLIENTE', 'IDCLIENTE');
    }

}
