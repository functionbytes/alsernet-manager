<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * AdminMenuService - Servicio centralizado para gestionar menús del panel administrativo
 *
 * Permite que cada módulo registre dinámicamente sus items de navegación
 * sin necesidad de modificar vistas o configuraciones centrales.
 */
class AdminMenuService
{
    /**
     * Almacena todos los items de menú registrados
     * Estructura:
     * [
     *   'mini' => ['module_id' => [...config...]],
     *   'sidebar' => ['sidebar_id' => ['title' => '...', 'items' => [...]]]
     * ]
     */
    private static array $menus = [];

    /**
     * Registrar un item en el mini-nav (navegación lateral izquierda con iconos)
     *
     * @param  string  $moduleId  ID único del módulo (ej: 'documents', 'marketing')
     * @param  array  $config  Configuración del item
     *                         - icon: clase del icono Font Awesome (ej: 'fa-file-pdf')
     *                         - tooltip: texto del tooltip (ej: 'Documentos')
     *                         - sidebar_id: ID del sidebar asociado (para abrir al clickear)
     *                         - order: orden de aparición (default: 999)
     *
     * @example
     * AdminMenuService::registerMiniItem('documents', [
     *     'icon' => 'fa-file-pdf',
     *     'tooltip' => 'Documentos',
     *     'sidebar_id' => 'documents',
     *     'order' => 10,
     * ]);
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
     *
     * @param  string  $sidebarId  ID único del sidebar (ej: 'configuracion', 'documents')
     * @param  array  $config  Configuración del sidebar
     *                         - title: título que aparece en el encabezado (ej: 'Configuración')
     *                         - items: array de items del menú, cada uno con:
     *                         - label: texto visible del item
     *                         - route: nombre de la ruta (ej: 'manager.documents.index')
     *                         - icon (opcional): icono del item
     *                         - children (opcional): subitems para menús jerárquicos
     *
     * @example
     * AdminMenuService::registerSidebar('documents', [
     *     'title' => 'Documentos',
     *     'items' => [
     *         ['label' => 'Listado', 'route' => 'manager.documents.index'],
     *         ['label' => 'Tipos', 'route' => 'manager.documents.types'],
     *         ['label' => 'Configuración', 'route' => 'manager.documents.settings'],
     *     ],
     * ]);
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

        self::$menus['sidebar'][$sidebarId] = $config;
    }

    /**
     * Obtener todos los items del mini-nav ordenados
     *
     * @return Collection Colección de items ordenados por 'order'
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
     * Verificar si un módulo está registrado en el mini-nav
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
     * Obtener toda la estructura de menús (útil para debugging)
     */
    public static function getAll(): array
    {
        return self::$menus;
    }

    /**
     * Limpiar todos los menús registrados (útil para testing)
     */
    public static function clear(): void
    {
        self::$menus = [];
    }
}
