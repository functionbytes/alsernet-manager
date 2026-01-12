<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Configuracion\Regfiscal;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla SUBCUENTA_CENT
 *
 * ÍNDICES DISPONIBLES:
 * PK_SUBCUENTA_CENT (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDSUBCUENTA
 *
 */
class Subcuenta extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'subcuenta_cent';
    protected $primaryKey = 'idsubcuenta';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'nsubcuenta', 'descripcion', 'idusuariocre', 'idusuariomod', 'idusuariobaj',
        'nif', 'domicilio', 'poblacion', 'provincia', 'cp',
        'intracomunitario', 'recargo_defecto', 'default', 'iva_defecto', 'default',
        'obligar_impuestos', 'idejercicio_contable', 'idempresa', 'estado', 'observacion',
        'idpais', 'retencion_defecto', 'default',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación inversa con Regfiscal
     */
    public function regfiscals()
    {
        return $this->hasMany(Regfiscal::class, 'idsubcuenta', 'idsubcuenta_cent');
    }


    /**
     * Relación: Subcuenta
     * ✅ Usa PK_SUBCUENTA_CENT (indexado)
     */
    public function subcuenta()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Subcuenta::class, 'IDSUBCUENTA', 'IDSUBCUENTA');
    }

    /**
     * Relación: EjercicioContable
     * ⚠️  SIN ÍNDICE en IDEJERCICIO_CONTABLE
     */
    public function ejercicioContable()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\EjercicioContable::class, 'IDEJERCICIO_CONTABLE', 'IDEJERCICIO_CONTABLE');
    }

    /**
     * Relación: Empresa
     * ⚠️  SIN ÍNDICE en IDEMPRESA
     */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Empresa::class, 'IDEMPRESA', 'IDEMPRESA');
    }

    /**
     * Relación: Pais
     * ⚠️  SIN ÍNDICE en IDPAIS
     */
    public function pais()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Pais::class, 'IDPAIS', 'IDPAIS');
    }

}
