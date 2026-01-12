<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla USUARIOSISTEMA
 *
 * ÍNDICES DISPONIBLES:
 * PK_USUARIOSISTEMA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDUSUARIOSISTEMA
 *
 */
class Usuariosistema extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'usuariosistema';
    protected $primaryKey = 'idusuariosistema';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'falta', 'estado', 'idusuariomod', 'login', 'password',
        'nivel', 'idempleado', 'controlriesgo', 'intentos', 'nbloqueos',
        'email', 'nombre',
    ];

    protected $casts = [
        'falta' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Usuariosistema
     * ✅ Usa PK_USUARIOSISTEMA (indexado)
     */
    public function usuariosistema()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Usuariosistema::class, 'IDUSUARIOSISTEMA', 'IDUSUARIOSISTEMA');
    }

}
