<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla BANCO
 *
 * ÍNDICES DISPONIBLES:
 * PK_BANCO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDBANCO
 *
 */
class Banco extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'banco';
    protected $primaryKey = 'idbanco';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'estado', 'descripcion', 'codigo', 'idusuariomod', 'idsubcuenta',
        'bic',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Banco
     * ✅ Usa PK_BANCO (indexado)
     */
    public function banco()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Banco::class, 'IDBANCO', 'IDBANCO');
    }

    /**
     * Relación: Subcuenta
     * ⚠️  SIN ÍNDICE en IDSUBCUENTA
     */
    public function subcuenta()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Subcuenta::class, 'IDSUBCUENTA', 'IDSUBCUENTA');
    }

}
