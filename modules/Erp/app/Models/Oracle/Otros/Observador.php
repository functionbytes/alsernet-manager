<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla OBSERVADOR
 *
 * ÍNDICES DISPONIBLES:
 * PK_IDOBSERVADOR (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDOBSERVADOR
 *
 */
class Observador extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'observador';
    protected $primaryKey = 'idobservador';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idusuariocre', 'idusuariomod', 'idusuariobaj', 'borrado', 'identificador',
        'tipo', 'idusuariosistema', 'participante',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Observador
     * ✅ Usa PK_IDOBSERVADOR (indexado)
     */
    public function observador()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Observador::class, 'IDOBSERVADOR', 'IDOBSERVADOR');
    }

    /**
     * Relación: Usuariosistema
     * ⚠️  SIN ÍNDICE en IDUSUARIOSISTEMA
     */
    public function usuariosistema()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Usuariosistema::class, 'IDUSUARIOSISTEMA', 'IDUSUARIOSISTEMA');
    }

}
