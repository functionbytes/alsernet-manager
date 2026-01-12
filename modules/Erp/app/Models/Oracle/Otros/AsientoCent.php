<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla ASIENTO_CENT
 *
 * ÍNDICES DISPONIBLES:
 * ✅ IDX_ASIENTO_CENT_IDDIARIO (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDDIARIO
 *
 * PK_ASIENTO_CENT (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDASIENTO
 *
 * PK_ASIENTO_CENT_DESC (UNIQUE)
 *    - Tipo: FUNCTION-BASED NORMAL
 *    - Columnas: SYS_NC00015$
 *
 */
class AsientoCent extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'asiento_cent';
    protected $primaryKey = 'idasiento';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'iddiario', 'idusuariocre', 'idusuariomod', 'idusuariobaj', 'estado',
        'fconfirmacion', 'fasiento', 'nasiento', 'fcontaplus', 'idalmacen',
        'nfactura', 'tipo', 'idexportacion', 'observaciones', 'fexpedicion',
        'generacion_id', 'manual', 'idusuario_autoriza', 'fautoriza', 'tipo_anomalia',
    ];

    protected $casts = [
        'fconfirmacion' => 'datetime',
        'fasiento' => 'datetime',
        'fcontaplus' => 'datetime',
        'fexpedicion' => 'datetime',
        'fautoriza' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Asiento
     * ✅ Usa PK_ASIENTO_CENT (indexado)
     */
    public function asiento()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\AsientoCent::class, 'IDASIENTO', 'IDASIENTO');
    }

    /**
     * Relación: Diario
     * ✅ Usa IDX_ASIENTO_CENT_IDDIARIO (indexado)
     */
    public function diario()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Diario::class, 'IDDIARIO', 'IDDIARIO');
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
