<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Lote\Tarifalote;
use Modules\Erp\Models\Oracle\Promocion\Lrebaja;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla REGPAIS
 *
 * ÍNDICES DISPONIBLES:
 * PK_REGPAIS (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDREGPAIS
 *
 * ⚠️  UK_REGPAIS_ZONA_FISCAL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: ZONA_FISCAL
 *
 */
class Regpais extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'regpais';
    protected $primaryKey = 'idregpais';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idpais', 'descripcion', 'idusuariomod', 'estado', 'datosregistrales',
        'datosregistrales_exento', 'zona_fiscal', 'cod_conta',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación inversa con Lrebaja
     */
    public function lrebajas()
    {
        return $this->hasMany(Lrebaja::class, 'idregpais', 'idregpais');
    }

    /**
     * Relación inversa con Tarifalote
     */
    public function tarifalotes()
    {
        return $this->hasMany(Tarifalote::class, 'idregpais', 'idregpais');
    }


    /**
     * Relación: Regpais
     * ✅ Usa PK_REGPAIS (indexado)
     */
    public function regpais()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Regpais::class, 'IDREGPAIS', 'IDREGPAIS');
    }

    /**
     * Relación: Pais
     * ⚠️  SIN ÍNDICE en IDPAIS
     */
    public function pais()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Pais::class, 'IDPAIS', 'IDPAIS');
    }

}
