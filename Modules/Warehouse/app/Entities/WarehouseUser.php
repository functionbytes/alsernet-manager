<?php

namespace Modules\Warehouse\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseUser extends Model
{
    use HasFactory;

    protected $table = 'warehouse_user';

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'is_default',
        'can_transfer',
        'can_inventory',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'can_transfer' => 'boolean',
            'can_inventory' => 'boolean',
        ];
    }

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Relación con el almacén
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
