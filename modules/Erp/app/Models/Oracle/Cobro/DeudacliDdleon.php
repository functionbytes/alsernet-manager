<?php

namespace Modules\Erp\Models\Oracle\Cobro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla DEUDACLI_DDLEON
 *
 * ÍNDICES DISPONIBLES:
 * ✅ INDX_DEUDACLI_DDL_IDALBARANCLI (NONUNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDALBARANCLI
 *
 * PK_DEUDACLI_DDLEON (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDDEUDACLI
 *
 */
class DeudacliDdleon extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'deudacli_ddleon';
    protected $primaryKey = 'iddeudacli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcobrocli', 'idalbarancli', 'idformapago', 'idusuariocre', 'idusuariomod',
        'idusuariobaj', 'estado', 'importe',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Deudacli
     * ✅ Usa PK_DEUDACLI_DDLEON (indexado)
     */
    public function deudacli()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\DeudacliCapthaya::class, 'IDDEUDACLI', 'IDDEUDACLI');
    }

    /**
     * Relación: Cobrocli
     * ⚠️  SIN ÍNDICE en IDCOBROCLI
     */
    public function cobrocli()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\CobrocliCapthaya::class, 'IDCOBROCLI', 'IDCOBROCLI');
    }

    /**
     * Relación: Albarancli
     * ✅ Usa INDX_DEUDACLI_DDL_IDALBARANCLI (indexado)
     */
    public function albarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\AlbarancliCapthaya::class, 'IDALBARANCLI', 'IDALBARANCLI');
    }

    /**
     * Relación: Formapago
     * ⚠️  SIN ÍNDICE en IDFORMAPAGO
     */
    public function formapago()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\Formapago::class, 'IDFORMAPAGO', 'IDFORMAPAGO');
    }

}
