<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Cobro\ExportacionCecaFppedcli;

/**
 * Modelo para la tabla EXPORTACION_CECA
 *
 * ÍNDICES DISPONIBLES:
 * PK_EXPORACION_CECA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDEXPORTACION_CECA
 *
 */
class ExportacionCeca extends Model
{
    protected $connection = 'oracle';
    protected $table = 'exportacion_ceca';
    protected $primaryKey = 'idexportacion_ceca';
    public $timestamps = false;

    protected $fillable = [
        'idusuario', 'nomfichero', 'ruta', 'fichero',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación inversa con ExportacionCecaFppedcli
     */
    public function exportacionCecaFppedclis()
    {
        return $this->hasMany(ExportacionCecaFppedcli::class, 'idexportacion_ceca', 'idexportacion_ceca');
    }


    /**
     * Relación: ExportacionCeca
     * ✅ Usa PK_EXPORACION_CECA (indexado)
     */
    public function exportacionCeca()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\ExportacionCeca::class, 'IDEXPORTACION_CECA', 'IDEXPORTACION_CECA');
    }

}
