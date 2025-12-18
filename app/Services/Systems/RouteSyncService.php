<?php

namespace App\Services\Systems;

use App\Models\AppRoute;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class RouteSyncService
{
    /**
     * Profile configuration mapping
     */
    protected array $profileMapping = [
        'manager' => 'managers.php',
        'callcenter' => 'callcenters.php',
        'shop' => 'shops.php',
        'warehouse' => 'warehouses.php',
        'administrative' => 'administratives.php',
        'returns' => 'returns.php',
    ];

    /**
     * Sync all routes from Laravel router to database
     */
    public function syncAllRoutes()
    {
        $changes = [
            'added' => [],
            'updated' => [],
            'deleted' => [],
            'total' => 0,
        ];

        try {
            // Get all Laravel routes
            $laravelRoutes = $this->extractLaravelRoutes();

            // Process each route
            foreach ($laravelRoutes as $routeData) {
                $hash = AppRoute::generateHash(
                    $routeData['name'],
                    $routeData['path'],
                    $routeData['method'],
                    $routeData['profile']
                );

                // Use updateOrCreate for safe, idempotent sync
                // This prevents duplicates and is atomic at database level
                $route = AppRoute::updateOrCreate(
                    ['name' => $routeData['name']], // Search criteria (UNIQUE)
                    array_merge($routeData, ['hash' => $hash]) // Data to update/create
                );

                // Track if it was created or updated
                if ($route->wasRecentlyCreated) {
                    $changes['added'][] = $route->name;
                    Log::info('Route added to database', ['route' => $route->name]);
                } else {
                    $changes['updated'][] = $route->name;
                    Log::info('Route updated in database', ['route' => $route->name]);
                }
                $changes['total']++;
            }

            // Delete routes that no longer exist
            $currentNames = collect($laravelRoutes)->pluck('name')->unique();
            $deletedRoutes = AppRoute::whereNotIn('name', $currentNames)->get();

            foreach ($deletedRoutes as $route) {
                Log::info('Route deleted from database', ['route' => $route->name]);
                $changes['deleted'][] = $route->name;
                $route->delete();
            }

            Log::info('Route synchronization completed', $changes);
            return $changes;
        } catch (\Exception $e) {
            Log::error('Error syncing routes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Extract all routes from Laravel's router
     */
    protected function extractLaravelRoutes()
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            // Skip non-important routes
            if ($this->shouldSkipRoute($route)) {
                continue;
            }

            // Get path using compatible method (handles different Laravel versions)
            $path = $this->getRoutePath($route);

            $routeData = [
                'name' => $route->getName(),
                'path' => $path,
                'method' => $this->getRouteMethods($route),
                'profile' => $this->detectProfile($route),
                'middleware' => $route->middleware(),
                'controller' => $this->getRouteController($route),
                'action' => $this->getRouteAction($route),
                'requires_auth' => $this->requiresAuth($route),
                'description' => null,
                'hash' => '',
            ];

            // Generate hash later with complete data
            $hash = AppRoute::generateHash(
                $routeData['name'],
                $routeData['path'],
                $routeData['method'],
                $routeData['profile']
            );
            $routeData['hash'] = $hash;

            $routes[] = $routeData;
        }

        return $routes;
    }

    /**
     * Determine if route should be skipped
     */
    protected function shouldSkipRoute($route)
    {
        // Skip routes without names
        if (!$route->getName()) {
            return true;
        }

        // Skip Laravel's built-in routes
        $skipPatterns = ['debugbar', 'ignition', 'laravel-websockets'];
        foreach ($skipPatterns as $pattern) {
            if (str_contains($route->getName(), $pattern)) {
                return true;
            }
        }

        // Skip API routes (handle separately)
        if (str_contains($this->getRoutePath($route), '/api/')) {
            return true;
        }

        return false;
    }

    /**
     * Get HTTP methods for a route
     */
    protected function getRouteMethods($route)
    {
        $methods = $route->methods;
        // Remove 'HEAD' method, keep only meaningful ones
        $methods = array_diff($methods, ['HEAD']);
        return implode('|', array_values($methods));
    }

    /**
     * Get route path (compatible with different Laravel versions)
     */
    protected function getRoutePath($route): string
    {
        // Try different methods based on Laravel version
        if (method_exists($route, 'getPath')) {
            return $route->getPath();
        } elseif (method_exists($route, 'getUri')) {
            return $route->getUri();
        } elseif (isset($route->uri)) {
            return $route->uri;
        } else {
            // Fallback: try to get from compiled route
            return $route->compiledRoute->getPath() ?? '/';
        }
    }

    /**
     * Detect which profile a route belongs to
     */
    protected function detectProfile($route)
    {
        $path = $this->getRoutePath($route);
        $name = $route->getName();

        // Check by path prefix
        foreach ($this->profileMapping as $profile => $file) {
            if (str_starts_with($path, '/' . $profile)) {
                return $profile;
            }
        }

        // Check by route name prefix
        if (str_contains($name, '.')) {
            $prefix = explode('.', $name)[0];
            if (array_key_exists($prefix, $this->profileMapping)) {
                return $prefix;
            }
        }

        return null;
    }

    /**
     * Extract controller from route
     */
    protected function getRouteController($route)
    {
        $action = $route->getAction();

        if (isset($action['controller'])) {
            if (is_string($action['controller'])) {
                return explode('@', $action['controller'])[0];
            } elseif (is_array($action['controller'])) {
                return get_class($action['controller'][0]);
            }
        }

        return null;
    }

    /**
     * Extract action from route
     */
    protected function getRouteAction($route)
    {
        $action = $route->getAction();

        if (isset($action['controller'])) {
            if (is_string($action['controller'])) {
                return explode('@', $action['controller'])[1] ?? null;
            } elseif (is_array($action['controller'])) {
                return $action['controller'][1] ?? null;
            }
        }

        return null;
    }

    /**
     * Check if route requires authentication
     */
    protected function requiresAuth($route)
    {
        $middleware = $route->middleware();
        return in_array('auth', $middleware) || in_array('auth:web', $middleware);
    }

    /**
     * Get routes by profile
     */
    public function getRoutesByProfile($profile)
    {
        return AppRoute::byProfile($profile)
            ->active()
            ->orderBy('path')
            ->get();
    }

    /**
     * Get all profiles
     */
    public function getProfiles()
    {
        return AppRoute::getProfiles();
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        return [
            'total_routes' => AppRoute::count(),
            'active_routes' => AppRoute::active()->count(),
            'by_profile' => AppRoute::select('profile')
                ->groupBy('profile')
                ->selectRaw('profile, count(*) as count')
                ->get()
                ->pluck('count', 'profile')
                ->toArray(),
            'by_method' => AppRoute::select('method')
                ->groupBy('method')
                ->selectRaw('method, count(*) as count')
                ->get()
                ->pluck('count', 'method')
                ->toArray(),
        ];
    }
}
