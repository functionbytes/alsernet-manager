<?php

namespace Modules\Erp\Models\Oracle\Catalogo;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla TIPOFOTOGRAFIA
 *
 * ÍNDICES DISPONIBLES:
 * PK_IDTIPOFOTOGRAFIA (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDTIPOFOTOGRAFIA
 *
 */
class Tipofotografia extends Model
{
    protected $connection = 'oracle';
    protected $table = 'tipofotografia';
    protected $primaryKey = 'idtipofotografia';
    public $timestamps = false;

    protected $fillable = [
        'descripcion', 'marcaaguaautomatica', 'fotounica', 'sufijo',
    ];

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación inversa con Fotografia
     */
    public function fotografias()
    {
        return $this->hasMany(Fotografia::class, 'idtipofotografia', 'idtipofotografia');
    }

    /**
     * Relación inversa con TamanoTipofotografia
     */
    public function tamanoTipofotografias()
    {
        return $this->hasMany(TamanoTipofotografia::class, 'idtipofotografia', 'idtipofotografia');
    }


    /**
     * Relación: Tipofotografia
     * ✅ Usa PK_IDTIPOFOTOGRAFIA (indexado)
     */
    public function tipofotografia()
    {
        return $this->belongsTo(\App\Models\Oracle\Catalogo\Tipofotografia::class, 'IDTIPOFOTOGRAFIA', 'IDTIPOFOTOGRAFIA');
    }

}
