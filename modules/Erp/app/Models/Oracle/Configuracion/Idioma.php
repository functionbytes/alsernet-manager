<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Lote\Lloteidioma;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla IDIOMA
 *
 * ÍNDICES DISPONIBLES:
 * PK_IDIOMA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDIDIOMA
 *
 */
class Idioma extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'idioma';
    protected $primaryKey = 'ididiomaantiguo';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'ididioma', 'descripcion', 'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación inversa con Lloteidioma
     */
    public function lloteidiomas()
    {
        return $this->hasMany(Lloteidioma::class, 'ididioma', 'ididioma');
    }


    /**
     * Relación: Idioma
     * ✅ Usa PK_IDIOMA (indexado)
     */
    public function idioma()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Idioma::class, 'IDIDIOMA', 'IDIDIOMA');
    }

}
