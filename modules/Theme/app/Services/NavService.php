<?php

namespace Modules\Theme\Services;

use Illuminate\Support\Collection;

/**
 * NavService - Servicio centralizado para gestionar la navegación del panel administrativo
 *
 * Permite que cada módulo registre dinámicamente sus items de navegación
 * sin necesidad de modificar vistas o configuraciones centrales.
 */
class NavService
{
    /**
     * Almacena todos los items de menú registrados
     */
    private static array $menus = [];

    /**
     * Registrar un item en el mini-nav (navegación lateral izquierda con iconos)
     */
    public static function registerMiniItem(string $moduleId, array $config): void
    {
        if (! isset(self::$menus['mini'])) {
            self::$menus['mini'] = [];
        }

        // Validar configuración requerida
        $required = ['icon', 'tooltip', 'sidebar_id'];
        foreach ($required as $field) {
            if (! isset($config[$field])) {
                throw new \InvalidArgumentException("Missing required field '{$field}' in mini-nav item '{$moduleId}'");
            }
        }

        // Asignar valores por defecto
        $config['id'] = $moduleId;
        $config['order'] = $config['order'] ?? 999;

        self::$menus['mini'][$moduleId] = $config;
    }

    /**
     * Registrar items en un sidebar (menú desplegable)
     * Si el sidebar ya existe, agrega una nueva sección con su propio título
     */
    public static function registerSidebar(string $sidebarId, array $config): void
    {
        if (! isset(self::$menus['sidebar'])) {
            self::$menus['sidebar'] = [];
        }

        // Validar configuración requerida
        if (! isset($config['title']) || ! isset($config['items'])) {
            throw new \InvalidArgumentException("Sidebar '{$sidebarId}' must have 'title' and 'items'");
        }

        // Si el sidebar ya existe, agregar una nueva sección en lugar de sobrescribir
        if (isset(self::$menus['sidebar'][$sidebarId])) {
            // Inicializar 'sections' si no existe
            if (! isset(self::$menus['sidebar'][$sidebarId]['sections'])) {
                // Si hay items legacy, convertir a sección
                $legacyItems = self::$menus['sidebar'][$sidebarId]['items'] ?? [];
                $legacyTitle = self::$menus['sidebar'][$sidebarId]['title'] ?? 'Menu';

                self::$menus['sidebar'][$sidebarId]['sections'] = [];

                if (! empty($legacyItems)) {
                    self::$menus['sidebar'][$sidebarId]['sections'][] = [
                        'title' => $legacyTitle,
                        'items' => $legacyItems,
                    ];
                }

                // Limpiar items y title legacy si existen
                if (isset(self::$menus['sidebar'][$sidebarId]['items'])) {
                    unset(self::$menus['sidebar'][$sidebarId]['items']);
                }
                if (isset(self::$menus['sidebar'][$sidebarId]['title'])) {
                    unset(self::$menus['sidebar'][$sidebarId]['title']);
                }
            }

            // Agregar nueva sección
            self::$menus['sidebar'][$sidebarId]['sections'][] = [
                'title' => $config['title'],
                'items' => $config['items'],
            ];
        } else {
            // Crear sidebar nuevo con estructura de secciones
            self::$menus['sidebar'][$sidebarId] = [
                'sections' => [
                    [
                        'title' => $config['title'],
                        'items' => $config['items'],
                    ],
                ],
            ];
        }
    }

    /**
     * Agregar items a un sidebar existente
     * Agrega items a la última sección. Útil cuando múltiples módulos quieren contribuir items al mismo sidebar
     */
    public static function addSidebarItems(string $sidebarId, array $items): void
    {
        if (! isset(self::$menus['sidebar'])) {
            self::$menus['sidebar'] = [];
        }

        if (! isset(self::$menus['sidebar'][$sidebarId])) {
            throw new \InvalidArgumentException("Sidebar '{$sidebarId}' does not exist. Register it first with registerSidebar().");
        }

        // Si usa estructura de secciones, agregar a la última sección
        if (isset(self::$menus['sidebar'][$sidebarId]['sections'])) {
            $lastSectionIndex = count(self::$menus['sidebar'][$sidebarId]['sections']) - 1;
            if ($lastSectionIndex >= 0) {
                self::$menus['sidebar'][$sidebarId]['sections'][$lastSectionIndex]['items'] = array_merge(
                    self::$menus['sidebar'][$sidebarId]['sections'][$lastSectionIndex]['items'],
                    $items
                );
            }
        } else {
            // Estructura legacy
            self::$menus['sidebar'][$sidebarId]['items'] = array_merge(
                self::$menus['sidebar'][$sidebarId]['items'] ?? [],
                $items
            );
        }
    }

    /**
     * Obtener todos los items del mini-nav ordenados
     */
    public static function getMiniItems(): Collection
    {
        $items = collect(self::$menus['mini'] ?? [])
            ->sortBy('order')
            ->values();

        return $items;
    }

    /**
     * Obtener un item específico del mini-nav
     */
    public static function getMiniItem(string $moduleId): ?array
    {
        return self::$menus['mini'][$moduleId] ?? null;
    }

    /**
     * Obtener un sidebar específico
     */
    public static function getSidebar(string $sidebarId): ?array
    {
        return self::$menus['sidebar'][$sidebarId] ?? null;
    }

    /**
     * Obtener todos los sidebars registrados
     */
    public static function getAllSidebars(): array
    {
        return self::$menus['sidebar'] ?? [];
    }

    /**
     * Obtener todos los items de navegación (compatible con NavigationService anterior)
     */
    public static function getNavigation(): array
    {
        $navigation = [];

        foreach (self::getAllSidebars() as $sidebarId => $sidebar) {
            $miniItem = self::getMiniItem($sidebarId);

            if ($miniItem) {
                // Soportar nueva estructura de secciones
                if (isset($sidebar['sections'])) {
                    // Extraer primer título de la primera sección
                    $firstSectionTitle = $sidebar['sections'][0]['title'] ?? $sidebarId;

                    $navigation[$sidebarId] = [
                        'id' => $sidebarId,
                        'title' => $firstSectionTitle,
                        'icon' => $miniItem['icon'],
                        'sections' => $sidebar['sections'],
                    ];
                } else {
                    // Estructura legacy
                    $navigation[$sidebarId] = [
                        'id' => $sidebarId,
                        'title' => $sidebar['title'] ?? $sidebarId,
                        'icon' => $miniItem['icon'],
                        'items' => $sidebar['items'] ?? [],
                    ];
                }
            }
        }

        return $navigation;
    }

    /**
     * Verificar si un módulo está registrado
     */
    public static function hasMiniItem(string $moduleId): bool
    {
        return isset(self::$menus['mini'][$moduleId]);
    }

    /**
     * Verificar si un sidebar está registrado
     */
    public static function hasSidebar(string $sidebarId): bool
    {
        return isset(self::$menus['sidebar'][$sidebarId]);
    }

    /**
     * Obtener toda la estructura de menús (debugging)
     */
    public static function getAll(): array
    {
        return self::$menus;
    }

    /**
     * Limpiar todos los menús (testing)
     */
    public static function clear(): void
    {
        self::$menus = [];
    }

    /**
     * Obtener mini-items que el usuario autenticado puede ver
     * Filtra por permisos de módulos (modules.view.{moduleId})
     */
    public static function getMiniItemsForUser(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect([]);
        }

        return self::getMiniItems()->filter(function ($item) use ($user) {
            $moduleId = $item['id'] ?? null;
            if (! $moduleId) {
                return true; // Si no tiene ID, mostrar
            }

            $permissionName = "modules.view.{$moduleId}";

            // Si el usuario es super-admin, mostrar siempre
            if ($user->hasRole('super-admin')) {
                return true;
            }

            // Verificar si el usuario tiene permiso para este módulo
            return $user->hasPermissionTo($permissionName);
        });
    }

    /**
     * Obtener sidebars que el usuario autenticado puede ver
     * Filtra por permisos de módulos (modules.view.{moduleId})
     */
    public static function getSidebarsForUser(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $sidebars = [];

        foreach (self::getAllSidebars() as $sidebarId => $sidebar) {
            $permissionName = "modules.view.{$sidebarId}";

            // Si el usuario es super-admin, mostrar todos
            if ($user->hasRole('super-admin')) {
                $sidebars[$sidebarId] = $sidebar;

                continue;
            }

            // Verificar si el usuario tiene permiso para este módulo
            if ($user->hasPermissionTo($permissionName)) {
                $sidebars[$sidebarId] = $sidebar;
            }
        }

        return $sidebars;
    }

    /**
     * Obtener navegación filtrada por permisos del usuario
     */
    public static function getNavigationForUser(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $navigation = [];
        $miniItems = self::getMiniItemsForUser();
        $sidebars = self::getSidebarsForUser();

        foreach ($miniItems as $miniItem) {
            $sidebarId = $miniItem['sidebar_id'] ?? null;

            if ($sidebarId && isset($sidebars[$sidebarId])) {
                $sidebar = $sidebars[$sidebarId];

                // Soportar nueva estructura de secciones
                if (isset($sidebar['sections'])) {
                    $firstSectionTitle = $sidebar['sections'][0]['title'] ?? $sidebarId;

                    $navigation[$sidebarId] = [
                        'id' => $sidebarId,
                        'title' => $firstSectionTitle,
                        'icon' => $miniItem['icon'],
                        'sections' => $sidebar['sections'],
                    ];
                } else {
                    // Estructura legacy
                    $navigation[$sidebarId] = [
                        'id' => $sidebarId,
                        'title' => $sidebar['title'] ?? $sidebarId,
                        'icon' => $miniItem['icon'],
                        'items' => $sidebar['items'] ?? [],
                    ];
                }
            }
        }

        return $navigation;
    }

    /**
     * Verificar si un usuario puede ver un módulo específico
     */
    public static function userCanAccessModule(string $moduleId, ?\Illuminate\Foundation\Auth\User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user) {
            return false;
        }

        // Super-admin siempre tiene acceso
        if ($user->hasRole('super-admin')) {
            return true;
        }

        $permissionName = "modules.view.{$moduleId}";

        return $user->hasPermissionTo($permissionName);
    }
}
