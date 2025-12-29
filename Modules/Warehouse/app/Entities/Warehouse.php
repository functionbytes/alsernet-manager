<?php

namespace Modules\Warehouse\Entities;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Warehouse extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'warehouses';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'uid',
        'code',
        'name',
        'description',
        'available',
        'created_at',
        'updated_at',
    ];

    /**
     * Casteo de tipos
     */
    protected $casts = [
        'available' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->logFillable()->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");
    }

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function scopeUid($query, $uid)
    {
        return $query->where('uid', $uid)->first();
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    /**
     * Relación con Pisos del Almacén
     */
    public function floors(): HasMany
    {
        return $this->hasMany(WarehouseFloor::class, 'warehouse_id', 'id');
    }

    /**
     * Relación con Ubicaciones (a través de Pisos)
     */
    public function locations(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class, 'warehouse_id', 'id');
    }

    /**
     * Relación many-to-many con Usuarios
     * Un almacén puede ser asignado a múltiples usuarios
     */
    public function users()
    {
        return $this->belongsToMany(
            'App\Models\User',
            'user_warehouse',
            'warehouse_id',
            'user_id'
        )->withPivot('is_default', 'can_transfer', 'can_inventory')
            ->withTimestamps();
    }

    /**
     * Obtener usuarios que pueden realizar inventarios en este almacén
     */
    public function inventoryUsers()
    {
        return $this->users()
            ->where('user_warehouse.can_inventory', true);
    }

    /**
     * Obtener usuarios que pueden transferir productos en este almacén
     */
    public function transferUsers()
    {
        return $this->users()
            ->where('user_warehouse.can_transfer', true);
    }

    /**
     * Auto-generar UUID si no existe
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = Str::uuid();
            }
        });
    }
}
