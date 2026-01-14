<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla EQUIVPUNTOEURO
 *
 * ÍNDICES DISPONIBLES:
 * PK_IDEQUIVPUNTOEURO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDEQUIVPUNTOEURO
 *
 */
class Equivpuntoeuro extends Model
{
    protected $connection = 'oracle';
    protected $table = 'equivpuntoeuro';
    protected $primaryKey = 'idequivpuntoeuro';
    public $timestamps = false;

    protected $fillable = [
        'fechalimite', 'factor_liquidacion', 'not', 'factor_generacion', 'not',
    ];

    protected $casts = [
        'fechalimite' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Equivpuntoeuro
     * ✅ Usa PK_IDEQUIVPUNTOEURO (indexado)
     */
    public function equivpuntoeuro()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Equivpuntoeuro::class, 'IDEQUIVPUNTOEURO', 'IDEQUIVPUNTOEURO');
    }

}
