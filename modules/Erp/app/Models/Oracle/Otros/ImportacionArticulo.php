<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla IMPORTACION_ARTICULO
 *
 * ÍNDICES DISPONIBLES:
 * PK_IMPORTACION_ARTICULO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDIMPORTACION_ARTICULO
 *
 */
class ImportacionArticulo extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'importacion_articulo';
    protected $primaryKey = 'idimportacion_articulo';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idusuariocre', 'idusuariomod', 'idusuariobaj', 'estado', 'fecha',
        'idproveedor', 'observaciones',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: ImportacionArticulo
     * ✅ Usa PK_IMPORTACION_ARTICULO (indexado)
     */
    public function importacionArticulo()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\ImportacionArticulo::class, 'IDIMPORTACION_ARTICULO', 'IDIMPORTACION_ARTICULO');
    }

    /**
     * Relación: Proveedor
     * ⚠️  SIN ÍNDICE en IDPROVEEDOR
     */
    public function proveedor()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Proveedor::class, 'IDPROVEEDOR', 'IDPROVEEDOR');
    }

}
