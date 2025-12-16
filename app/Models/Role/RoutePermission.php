<?php

namespace App\Models\Role;

use App\Models\AppRoute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutePermission extends Model
{
    protected $table = 'route_permissions';

    protected $fillable = [
        'route_id',
        'permission_id',
    ];

    public $timestamps = false;

    /**
     * Relationship: RoutePermission belongs to AppRoute
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(AppRoute::class, 'route_id');
    }

    /**
     * Relationship: RoutePermission belongs to Permission
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(\Spatie\Permission\Models\Permission::class, 'permission_id');
    }
}
