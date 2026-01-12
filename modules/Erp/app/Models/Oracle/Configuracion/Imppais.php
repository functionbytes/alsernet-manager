<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla IMPPAIS
 *
 * ÍNDICES DISPONIBLES:
 * PK_IMPPAIS (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDIMPPAIS
 *
 */
class Imppais extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'imppais';
    protected $primaryKey = 'idimppais';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idimpuesto', 'idregpais', 'valoriva', 'not', 'recargo',
        'not', 'idusuariomod', 'estado', 'idscivarep', 'idscivasop',
        'idscrecargoc', 'idscrecargov', 'idscivarep_sinrec', 'idscivasop_sinrec',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Imppais
     * ✅ Usa PK_IMPPAIS (indexado)
     */
    public function imppais()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Imppais::class, 'IDIMPPAIS', 'IDIMPPAIS');
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
