<?php

namespace Modules\Erp\Models\Oracle\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla FAMILIA_CL
 *
 * ÍNDICES DISPONIBLES:
 * PK_FAMILIA_CL (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDFAMILIA_CL
 *
 */
class FamiliaCl extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'familia_cl';
    protected $primaryKey = 'idfamilia_cl';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'idcategoria_cl', 'estado', 'idusuariocre', 'idusuariomod', 'idusuariobaj',
        'descripcion', 'desc_corta', 'sonarmas', 'sonarmasfogueo', 'soncartuchos',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: FamiliaCl
     * ✅ Usa PK_FAMILIA_CL (indexado)
     */
    public function familiaCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\FamiliaCl::class, 'IDFAMILIA_CL', 'IDFAMILIA_CL');
    }

    /**
     * Relación: CategoriaCl
     * ⚠️  SIN ÍNDICE en IDCATEGORIA_CL
     */
    public function categoriaCl()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\CategoriaCl::class, 'IDCATEGORIA_CL', 'IDCATEGORIA_CL');
    }

    /**
     * Relación inversa: SupplierFamilies (sincronización Supplier)
     */
    public function supplierFamilies(): HasMany
    {
        return $this->hasMany(\Modules\Supplier\Entities\SupplierFamily::class, 'erp_family_id', 'idfamilia_cl');
    }

}
