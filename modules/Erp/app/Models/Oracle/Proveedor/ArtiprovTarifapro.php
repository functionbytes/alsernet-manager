<?php

namespace Modules\Erp\Models\Oracle\Proveedor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla ARTIPROV_TARIFAPRO
 *
 * ÍNDICES DISPONIBLES:
 * PK_ARTIPROV_TARIFAPRO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTIPROV_TARIFAPRO
 *
 * ⚠️  UK_ARTIPROV_TARIFAPRO (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDARTIPROV, ESTADO, FECHA
 *
 */
class ArtiprovTarifapro extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'artiprov_tarifapro';
    protected $primaryKey = 'idartiprov_tarifapro';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idartiprov', 'idusuariocre', 'idusuariomod', 'idusuariobaj', 'estado',
        'fecha', 'pcosto', 'not', 'dto1', 'not',
        'dto2', 'not',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: ArtiprovTarifapro
     * ✅ Usa PK_ARTIPROV_TARIFAPRO (indexado)
     */
    public function artiprovTarifapro()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\ArtiprovTarifapro::class, 'IDARTIPROV_TARIFAPRO', 'IDARTIPROV_TARIFAPRO');
    }

    /**
     * Relación: Artiprov
     * ✅ Usa UK_ARTIPROV_TARIFAPRO (indexado)
     */
    public function artiprov()
    {
        return $this->belongsTo(\App\Models\Oracle\Proveedor\Artiprov::class, 'IDARTIPROV', 'IDARTIPROV');
    }

    /**
     * Relación inversa: SupplierProductPrices (sincronización Supplier)
     */
    public function supplierProductPrices(): HasMany
    {
        return $this->hasMany(\Modules\Supplier\Entities\SupplierProductPrice::class, 'erp_price_id', 'idartiprov_tarifapro');
    }

}
