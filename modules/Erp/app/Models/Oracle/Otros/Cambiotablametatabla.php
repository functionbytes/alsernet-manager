<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla CAMBIOTABLAMETATABLA
 *
 * ÍNDICES DISPONIBLES:
 * ⚠️  PC_CAMBIOTABLAMETATABLA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDCAMBIOTABLAMETATABLA
 *
 */
class Cambiotablametatabla extends Model
{
    protected $connection = 'oracle';
    protected $table = 'cambiotablametatabla';
    protected $primaryKey = 'idcambiotablametatabla';
    public $timestamps = false;

    protected $fillable = [
        'nombrereal', 'nombreficticio', 'campoclave',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Cambiotablametatabla
     * ✅ Usa PC_CAMBIOTABLAMETATABLA (indexado)
     */
    public function cambiotablametatabla()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Cambiotablametatabla::class, 'IDCAMBIOTABLAMETATABLA', 'IDCAMBIOTABLAMETATABLA');
    }

}
