<?php

namespace Modules\Erp\Models\Oracle\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Otros\CambioPublicidadEmail;
use Modules\Erp\Models\Oracle\Promocion\BonoPromocion;
use Modules\Erp\Models\Oracle\Promocion\Promocion;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla CATALOGO
 *
 * ÍNDICES DISPONIBLES:
 * PK_CATALOGO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCATALOGO
 *
 */
class Catalogo extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'catalogo';
    protected $primaryKey = 'idcatalogo_web';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcatalogo', 'descripcion', 'observacion', 'idusuariocre', 'idusuariomod',
        'idusuariobaja', 'estado', 'codigoconta', 'descripcioncorta', 'ididioma',
        'idarticulo_entregacuenta', 'importe_servir_parcial',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación inversa con BonoPromocion
     */
    public function bonoPromocions()
    {
        return $this->hasMany(BonoPromocion::class, 'idcatalogo_consumo', 'idcatalogo');
    }

    /**
     * Relación inversa con CambioPublicidadEmail
     */
    public function cambioPublicidadEmails()
    {
        return $this->hasMany(CambioPublicidadEmail::class, 'idcatalogo', 'idcatalogo');
    }

    /**
     * Relación inversa con Promocion
     */
    public function promocions()
    {
        return $this->hasMany(Promocion::class, 'idcatalogo_consumo', 'idcatalogo');
    }


    /**
     * Relación: Catalogo
     * ✅ Usa PK_CATALOGO (indexado)
     */
    public function catalogo()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\Catalogo::class, 'IDCATALOGO', 'IDCATALOGO');
    }

    /**
     * Relación: Idioma
     * ⚠️  SIN ÍNDICE en IDIDIOMA
     */
    public function idioma()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Idioma::class, 'IDIDIOMA', 'IDIDIOMA');
    }

}
