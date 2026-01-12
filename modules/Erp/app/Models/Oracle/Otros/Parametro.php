<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla PARAMETRO
 *
 * ÍNDICES DISPONIBLES:
 * PK_PARAMETRO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPARAMETRO
 *
 */
class Parametro extends Model
{
    protected $connection = 'oracle';
    protected $table = 'parametro';
    protected $primaryKey = 'idparametro';
    public $timestamps = false;

    protected $fillable = [
        'idgrupo_parametro', 'descripcion', 'valor', 'tipo', 'seguridad',
        'grupo', 'clave', 'borrado', 'traduccion',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Parametro
     * ✅ Usa PK_PARAMETRO (indexado)
     */
    public function parametro()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Parametro::class, 'IDPARAMETRO', 'IDPARAMETRO');
    }

}
