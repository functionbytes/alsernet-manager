<?php

namespace Modules\Erp\Models\Oracle\Albaran;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para la tabla SERIEALBARANCLI_DDLEON
 *
 * ÍNDICES DISPONIBLES:
 * PK_SERIEALBARANCLI_DDLEON (UNIQUE)
 *    - Tipo: NORMAL
 *    - Columnas: IDSERIEALBARANCLI
 *
 */
class SeriealbarancliDdleon extends Model
{
    use SoftDeletes;

    protected $connection = 'oracle';
    protected $table = 'seriealbarancli_ddleon';
    protected $primaryKey = 'idseriealbarancli';
    public $timestamps = true;
    const CREATED_AT = 'fcreacion';
    const UPDATED_AT = 'fmodificacion';
    const DELETED_AT = 'fbaja';

    protected $fillable = [
        'estado', 'idusuariomod', 'prox_num', 'descripcion', 'descripcioncorta',
        'idcaja', 'idalmacen', 'idserie', 'idempresa', 'fdesde',
        'fhasta', 'rectificativa', 'pordefecto', 'prox_num_fact_simpl', 'tipo',
    ];

    protected $casts = [
        'fdesde' => 'datetime',
        'fhasta' => 'datetime',
        'estado' => 'boolean',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: Seriealbarancli
     * ✅ Usa PK_SERIEALBARANCLI_DDLEON (indexado)
     */
    public function seriealbarancli()
    {
        return $this->belongsTo(\App\Models\Oracle\Albaran\SeriealbarancliCapthaya::class, 'IDSERIEALBARANCLI', 'IDSERIEALBARANCLI');
    }

    /**
     * Relación: Almacen
     * ⚠️  SIN ÍNDICE en IDALMACEN
     */
    public function almacen()
    {
        return $this->belongsTo(\App\Models\Oracle\Configuracion\Almacen::class, 'IDALMACEN', 'IDALMACEN');
    }

    /**
     * Relación: Serie
     * ⚠️  SIN ÍNDICE en IDSERIE
     */
    public function serie()
    {
        return $this->belongsTo(\App\Models\Oracle\Serie\Serie::class, 'IDSERIE', 'IDSERIE');
    }

    /**
     * Relación: Empresa
     * ⚠️  SIN ÍNDICE en IDEMPRESA
     */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Oracle\Otros\Empresa::class, 'IDEMPRESA', 'IDEMPRESA');
    }

}
