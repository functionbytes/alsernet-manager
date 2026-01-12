<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Catalogo\Modelo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla MARCA
 *
 * ÍNDICES DISPONIBLES:
 * PK_MARCA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDMARCA
 *
 */
class Marca extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'marca';
    protected $primaryKey = 'idmarca';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'descripcion', 'idusuariomod', 'idusuariocre', 'idusuariobaj', 'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación inversa con Modelo
     */
    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'idmarca', 'idmarca');
    }


    /**
     * Relación: Marca
     * ✅ Usa PK_MARCA (indexado)
     */
    public function marca()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Marca::class, 'IDMARCA', 'IDMARCA');
    }

}
