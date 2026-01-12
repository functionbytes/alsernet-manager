<?php

namespace Modules\Erp\Models\Oracle\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Configuracion\Marca;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla MODELO
 *
 * ÍNDICES DISPONIBLES:
 * PK_MODELO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDMODELO
 *
 */
class Modelo extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'modelo';
    protected $primaryKey = 'idmodelo';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idusuariocre', 'idusuariomod', 'idusuariobaj', 'estado', 'codigo',
        'idgrupo_cl', 'nombre', 'nombre_web', 'descripcion', 'estado_publicado_web',
        'idmarca', 'venta_telefono', 'precio_consultar_ficha',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con Marca
     */
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'idmarca', 'idmarca');
    }


    /**
     * Relación: Modelo
     * ✅ Usa PK_MODELO (indexado)
     */
    public function modelo()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\Modelo::class, 'IDMODELO', 'IDMODELO');
    }

    /**
     * Relación: GrupoCl
     * ⚠️  SIN ÍNDICE en IDGRUPO_CL
     */
    public function grupoCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\GrupoCl::class, 'IDGRUPO_CL', 'IDGRUPO_CL');
    }

}
