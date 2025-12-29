<?php

namespace Modules\Warehouse\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NavigationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $user = Auth::user();
        $config = config('warehouse.navigation', []);

        // Get sidebar navigation items
        $navigationItems = $this->buildNavigation($user, $config['sidebar'] ?? []);

        $view->with('warehouseNavigation', $navigationItems);
    }

    /**
     * Build navigation items based on user permissions.
     */
    private function buildNavigation($user, array $sectionConfig): array
    {
        if (empty($sectionConfig)) {
            return [];
        }

        $items = $sectionConfig['items'] ?? [];
        $filtered = [];

        foreach ($items as $item) {
            // Skip if user doesn't have permission
            if (isset($item['permission']) && !$this->userHasPermission($user, $item['permission'])) {
                continue;
            }

            // Mark as active if current route matches
            $item['active'] = $this->isRouteActive($item['route'] ?? null);

            $filtered[] = $item;
        }

        return $filtered;
    }

    /**
     * Check if user has the given permission.
     */
    private function userHasPermission($user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        // Super admin can do everything
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * Check if the given route is currently active.
     */
    private function isRouteActive(?string $route): bool
    {
        if (!$route) {
            return false;
        }

        return request()->routeIs($route.'*');
    }
}
