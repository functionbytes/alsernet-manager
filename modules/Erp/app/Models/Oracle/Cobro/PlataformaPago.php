<?php

namespace Modules\Erp\Models\Oracle\Cobro;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla PLATAFORMA_PAGO
 *
 * ÍNDICES DISPONIBLES:
 * PK_PLATAFORMA_PAGO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDPLATAFORMA_PAGO
 *
 * ⚠️  UK_PLATAFORMA_PAGO_DESC (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: DESCRIPCION
 *
 */
class PlataformaPago extends Model
{
    protected $connection = 'oracle';
    protected $table = 'plataforma_pago';
    protected $primaryKey = 'idplataforma_pago';
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación inversa con Formapago
     */
    public function formapagos()
    {
        return $this->hasMany(Formapago::class, 'idplataforma_pago', 'idplataforma_pago');
    }


    /**
     * Relación: PlataformaPago
     * ✅ Usa PK_PLATAFORMA_PAGO (indexado)
     */
    public function plataformaPago()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\PlataformaPago::class, 'IDPLATAFORMA_PAGO', 'IDPLATAFORMA_PAGO');
    }

}
