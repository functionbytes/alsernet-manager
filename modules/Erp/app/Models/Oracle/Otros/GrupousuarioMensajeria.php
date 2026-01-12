<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla GRUPOUSUARIO_MENSAJERIA
 *
 * ÍNDICES DISPONIBLES:
 * PK_IDGRUPOUSUARIO_MENSAJERIA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDGRUPOUSUARIO_MENSAJERIA
 *
 */
class GrupousuarioMensajeria extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'grupousuario_mensajeria';
    protected $primaryKey = 'idgrupousuario_mensajeria';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idusuariocre', 'idusuariomod', 'idusuariobaj', 'borrado', 'idgrupo_mensajeria',
        'idusuariosistema',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: GrupousuarioMensajeria
     * ✅ Usa PK_IDGRUPOUSUARIO_MENSAJERIA (indexado)
     */
    public function grupousuarioMensajeria()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\GrupousuarioMensajeria::class, 'IDGRUPOUSUARIO_MENSAJERIA', 'IDGRUPOUSUARIO_MENSAJERIA');
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
