<?php

namespace Modules\Erp\Models\Oracle\Cobro;

use Illuminate\Database\Eloquent\Model;
use Modules\Erp\Models\Oracle\Configuracion\ExportacionCeca;

/**
 * Modelo para la tabla EXPORTACION_CECA_FPPEDCLI
 *
 * ÍNDICES DISPONIBLES:
 * PK_EXPORTACION_CECA_FPPEDCLI (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDEXPORTACION_CECA_FPPEDCLI
 *
 * ⚠️  UK_EXPORTACION_CECA_FPPEDCLI (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDEXPORTACION_CECA, IDFPPEDCLI
 *
 */
class ExportacionCecaFppedcli extends Model
{
    protected $connection = 'oracle';
    protected $table = 'exportacion_ceca_fppedcli';
    protected $primaryKey = 'idexportacion_ceca_fppedcli';
    public $timestamps = false;

    protected $fillable = [
        'idexportacion_ceca', 'idfppedcli', 'serie', 'numeropedido', 'numerotarjeta',
        'importe',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación con ExportacionCeca
     */
    public function exportacion_ceca()
    {
        return $this->belongsTo(ExportacionCeca::class, 'idexportacion_ceca', 'idexportacion_ceca');
    }


    /**
     * Relación: ExportacionCeca
     * ✅ Usa UK_EXPORTACION_CECA_FPPEDCLI (indexado)
     */
    public function exportacionCeca()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\ExportacionCeca::class, 'IDEXPORTACION_CECA', 'IDEXPORTACION_CECA');
    }


    /**
     * Relación: ExportacionCecaFppedcli
     * ✅ Usa PK_EXPORTACION_CECA_FPPEDCLI (indexado)
     */
    public function exportacionCecaFppedcli()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\ExportacionCecaFppedcli::class, 'IDEXPORTACION_CECA_FPPEDCLI', 'IDEXPORTACION_CECA_FPPEDCLI');
    }

    /**
     * Relación: Fppedcli
     * ⚠️  SIN ÍNDICE en IDFPPEDCLI
     */
    public function fppedcli()
    {
        return $this->belongsTo(\App\Models\Oracle\Cobro\FppedcliCapthaya::class, 'IDFPPEDCLI', 'IDFPPEDCLI');
    }

}
