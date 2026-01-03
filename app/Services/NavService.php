<?php

namespace App\Services;

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
     * Si el sidebar ya existe, lo sobrescribe
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

        // Si ya existe, agregar items al existente en lugar de sobrescribir
        if (isset(self::$menus['sidebar'][$sidebarId])) {
            self::$menus['sidebar'][$sidebarId]['items'] = array_merge(
                self::$menus['sidebar'][$sidebarId]['items'],
                $config['items']
            );
        } else {
            self::$menus['sidebar'][$sidebarId] = $config;
        }
    }

    /**
     * Agregar items a un sidebar existente
     * Útil cuando múltiples módulos quieren contribuir items al mismo sidebar
     */
    public static function addSidebarItems(string $sidebarId, array $items): void
    {
        if (! isset(self::$menus['sidebar'])) {
            self::$menus['sidebar'] = [];
        }

        if (! isset(self::$menus['sidebar'][$sidebarId])) {
            throw new \InvalidArgumentException("Sidebar '{$sidebarId}' does not exist. Register it first with registerSidebar().");
        }

        self::$menus['sidebar'][$sidebarId]['items'] = array_merge(
            self::$menus['sidebar'][$sidebarId]['items'],
            $items
        );
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
                $navigation[$sidebarId] = [
                    'id' => $sidebarId,
                    'title' => $sidebar['title'],
                    'icon' => $miniItem['icon'],
                    'items' => $sidebar['items'],
                ];
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
}
