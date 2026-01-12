<?php

namespace Modules\Warehouse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWarehouseAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si es super-admin, permitir acceso
        if (auth()->user()->hasRole('super-admin')) {
            return $next($request);
        }

        // Si tiene permiso warehouse.access, permitir
        if (auth()->user()->hasPermissionTo('warehouse.access')) {
            return $next($request);
        }

        // Si no tiene permisos, denegar acceso
        abort(403, 'No tienes permiso para acceder al módulo de almacenes');
    }
}
