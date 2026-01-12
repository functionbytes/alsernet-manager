<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla GRUPOOBJETO_OBJETO
 *
 * ÍNDICES DISPONIBLES:
 * PK_GRUPOOBJETO_OBJETO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDGRUPOOBJETO, IDOBJETO
 *
 */
class GrupoobjetoObjeto extends Model
{
    protected $connection = 'oracle';
    protected $table = 'grupoobjeto_objeto';
    protected $primaryKey = 'idgrupoobjeto';
    public $timestamps = false;

    protected $fillable = [
        'idobjeto',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Grupoobjeto
     * ✅ Usa PK_GRUPOOBJETO_OBJETO (indexado)
     */
    public function grupoobjeto()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\GrupoObjetos::class, 'IDGRUPOOBJETO', 'IDGRUPOOBJETO');
    }

    /**
     * Relación: Objeto
     * ⚠️  SIN ÍNDICE en IDOBJETO
     */
    public function objeto()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Objeto::class, 'IDOBJETO', 'IDOBJETO');
    }

}
