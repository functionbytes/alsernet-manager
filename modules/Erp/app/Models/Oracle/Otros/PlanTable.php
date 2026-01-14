<?php

namespace Modules\Erp\Models\Oracle\Otros;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla PLAN_TABLE
 */
class PlanTable extends Model
{
    protected $connection = 'oracle';
    protected $table = 'plan_table';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'statement_id', 'plan_id', 'timestamp', 'remarks', 'operation',
        'options', 'object_node', 'object_owner', 'object_name', 'object_alias',
        'object_instance', 'object_type', 'optimizer', 'search_columns', 'parent_id',
        'depth', 'position', 'cost', 'cardinality', 'bytes',
        'other_tag', 'partition_start', 'partition_stop', 'partition_id', 'other',
        'distribution', 'cpu_cost', 'io_cost', 'temp_space', 'access_predicates',
        'filter_predicates', 'projection', 'time', 'qblock_name',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];


    // ========================================
    // Relaciones
    // ========================================

    /**
     * Relación: WAyudas
     * ⚠️  SIN ÍNDICE en ID
     */
    public function wAyudas()
    {
        return $this->belongsTo(\App\Models\Oracle\Web\WAyudas::class, 'ID', 'ID');
    }

}
