<?php

namespace Modules\Erp\Models\Oracle\Promocion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla LPROMOCIONEXCLUIDO
 *
 * ÍNDICES DISPONIBLES:
 * PK_LPROMOCIONEXCLUIDO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDLPROMOCIONEXCLUIDO
 *
 */
class Lpromocionexcluido extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'lpromocionexcluido';
    protected $primaryKey = 'idlpromocionexcluido';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idpromocion', 'idsubfamilia_cl', 'estado', 'idusuariocre', 'idusuariomod',
        'idusuariobaj',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Lpromocionexcluido
     * ✅ Usa PK_LPROMOCIONEXCLUIDO (indexado)
     */
    public function lpromocionexcluido()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Lpromocionexcluido::class, 'IDLPROMOCIONEXCLUIDO', 'IDLPROMOCIONEXCLUIDO');
    }

    /**
     * Relación: Promocion
     * ⚠️  SIN ÍNDICE en IDPROMOCION
     */
    public function promocion()
    {
        return $this->belongsTo(\App\Models\Oracle\Promocion\Promocion::class, 'IDPROMOCION', 'IDPROMOCION');
    }

    /**
     * Relación: SubfamiliaCl
     * ⚠️  SIN ÍNDICE en IDSUBFAMILIA_CL
     */
    public function subfamiliaCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\SubfamiliaCl::class, 'IDSUBFAMILIA_CL', 'IDSUBFAMILIA_CL');
    }

}
