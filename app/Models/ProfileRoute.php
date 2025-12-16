<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileRoute extends Model
{
    protected $table = 'profile_routes';

    protected $fillable = [
        'profile',
        'dashboard_route',
        'description',
    ];

    /**
     * Get the dashboard route for a specific profile
     */
    public static function getRoute($profile): ?string
    {
        return self::where('profile', $profile)->value('dashboard_route');
    }

    /**
     * Seed default profile routes
     */
    public static function seedDefaults()
    {
        $defaults = [
            [
                'profile' => 'manager',
                'dashboard_route' => 'manager.dashboard',
                'description' => 'Dashboard para gerentes',
            ],
            [
                'profile' => 'callcenter',
                'dashboard_route' => 'callcenter.dashboard',
                'description' => 'Dashboard para call center',
            ],
            [
                'profile' => 'inventory',
                'dashboard_route' => 'warehouse.dashboard',
                'description' => 'Dashboard para inventario',
            ],
            [
                'profile' => 'warehouse',
                'dashboard_route' => 'warehouse.dashboard',
                'description' => 'Dashboard para almacén',
            ],
            [
                'profile' => 'shop',
                'dashboard_route' => 'shop.dashboard',
                'description' => 'Dashboard para tienda',
            ],
            [
                'profile' => 'administrative',
                'dashboard_route' => 'administrative.dashboard',
                'description' => 'Dashboard para administrativo',
            ],
        ];

        foreach ($defaults as $route) {
            self::updateOrCreate(
                ['profile' => $route['profile']],
                $route
            );
        }
    }
}
