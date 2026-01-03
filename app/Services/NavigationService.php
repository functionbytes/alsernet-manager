<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class NavigationService
{
    protected array $navigation = [];

    public function __construct()
    {
        $this->loadNavigation();
    }

    /**
     * Cargar configuración de navegación de módulos activados
     */
    protected function loadNavigation(): void
    {
        // Cargar navegación base
        $this->navigation = [
            'dashboard' => $this->getDashboardNav(),
            'ecommerce' => $this->getEcommerceNav(),
            'marketing' => $this->getMarketingNav(),
            'usuarios' => $this->getUsuariosNav(),
            'soporte' => $this->getSoporteNav(),
            'bodega' => $this->getBodegaNav(),
            'configuracion' => $this->getConfiguracionNav(),
            'acceso' => $this->getAccesoNav(),
        ];
    }

    /**
     * Generar URL de ruta de forma segura, manejando parámetros faltantes
     */
    protected function safeRoute(string $routeName): ?string
    {
        try {
            if (! Route::has($routeName)) {
                return null;
            }

            return route($routeName);
        } catch (\Exception $e) {
            // Si la ruta requiere parámetros, retorna null
            return null;
        }
    }

    /**
     * Obtener navegación del Dashboard
     */
    protected function getDashboardNav(): array
    {
        $items = [];

        $url = $this->safeRoute('manager.dashboard');
        if ($url) {
            $items[] = [
                'label' => 'Dashboard',
                'url' => $url,
                'active' => Route::currentRouteName() === 'manager.dashboard',
            ];
        }

        return [
            'id' => 'dashboard',
            'icon' => 'fa-chart-line',
            'title' => 'Dashboard',
            'items' => $items,
        ];
    }

    /**
     * Obtener navegación de E-commerce
     */
    protected function getEcommerceNav(): array
    {
        $items = [];

        // Suscripciones
        $url = $this->safeRoute('manager.subscribers');
        if ($url) {
            $items[] = [
                'label' => 'Suscriptiones',
                'url' => $url,
                'active' => str_contains(Route::currentRouteName(), 'subscribers'),
            ];
        }

        $url = $this->safeRoute('manager.subscribers.lists');
        if ($url) {
            $items[] = [
                'label' => 'Listas',
                'url' => $url,
                'active' => str_contains(Route::currentRouteName(), 'subscribers.lists'),
            ];
        }

        $url = $this->safeRoute('manager.subscribers.conditions');
        if ($url) {
            $items[] = [
                'label' => 'Estados',
                'url' => $url,
                'active' => str_contains(Route::currentRouteName(), 'subscribers.conditions'),
            ];
        }

        // Productos
        $url = $this->safeRoute('manager.inventaries');
        if ($url) {
            $items[] = [
                'label' => 'Productos',
                'url' => $url,
                'active' => str_contains(Route::currentRouteName(), 'inventaries'),
            ];
        }

        // Tiendas
        $url = $this->safeRoute('manager.shops');
        if ($url) {
            $items[] = [
                'label' => 'Tiendas',
                'url' => $url,
                'active' => str_contains(Route::currentRouteName(), 'shops'),
            ];
        }

        return [
            'id' => 'ecommerce',
            'icon' => 'fa-bag-shopping',
            'title' => 'E-commerce',
            'items' => $items,
        ];
    }

    /**
     * Obtener navegación de Marketing/Campañas
     */
    protected function getMarketingNav(): array
    {
        $items = [];

        // Campañas
        $url = $this->safeRoute('manager.campaigns');
        if ($url) {
            $items[] = [
                'label' => 'Campañas',
                'url' => $url,
                'active' => str_contains(Route::currentRouteName(), 'campaigns'),
            ];
        }

        $url = $this->safeRoute('manager.maillists');
        if ($url) {
            $items[] = [
                'label' => 'Listas',
                'url' => $url,
                'active' => str_contains(Route::currentRouteName(), 'maillists'),
            ];
        }

        // Plantillas
        $url = $this->safeRoute('manager.templates');
        if ($url) {
            $items[] = [
                'label' => 'Plantillas campaña',
                'url' => $url,
                'active' => str_contains(Route::currentRouteName(), 'templates'),
            ];
        }

        $url = $this->safeRoute('manager.layouts');
        if ($url) {
            $items[] = [
                'label' => 'Plantilla correos',
                'url' => $url,
                'active' => str_contains(Route::currentRouteName(), 'layouts'),
            ];
        }

        return [
            'id' => 'marketing',
            'icon' => 'fa-bullhorn',
            'title' => 'Marketing',
            'items' => $items,
        ];
    }

    /**
     * Obtener navegación de Usuarios
     */
    protected function getUsuariosNav(): array
    {
        $items = [];

        $url = $this->safeRoute('manager.users');
        if ($url) {
            $items[] = [
                'label' => 'Usuarios',
                'url' => $url,
                'active' => str_contains(Route::currentRouteName(), 'users'),
            ];
        }

        return [
            'id' => 'usuarios',
            'icon' => 'fa-users',
            'title' => 'Usuarios',
            'items' => $items,
        ];
    }

    /**
     * Obtener navegación de Soporte (Helpdesk)
     */
    protected function getSoporteNav(): array
    {
        $items = [];

        // Conversaciones
        if ($this->routeExists('manager.helpdesk.conversations.show')) {
            $items[] = [
                'label' => 'Conversaciones',
                'url' => route('manager.helpdesk.conversations.show', 1),
                'active' => str_contains(Route::currentRouteName(), 'helpdesk.conversations'),
            ];
        }

        if ($this->routeExists('manager.helpdesk.helpcenter.index')) {
            $items[] = [
                'label' => 'Centro de Ayuda',
                'url' => route('manager.helpdesk.helpcenter.index'),
                'active' => str_contains(Route::currentRouteName(), 'helpdesk.helpcenter'),
            ];
        }

        // Tickets
        if ($this->routeExists('manager.helpdesk.tickets.index')) {
            $items[] = [
                'label' => 'Gestión de Tickets',
                'url' => route('manager.helpdesk.tickets.index'),
                'active' => str_contains(Route::currentRouteName(), 'helpdesk.tickets'),
            ];
        }

        if ($this->routeExists('manager.helpdesk.customers.index')) {
            $items[] = [
                'label' => 'Clientes',
                'url' => route('manager.helpdesk.customers.index'),
                'active' => str_contains(Route::currentRouteName(), 'helpdesk.customers'),
            ];
        }

        return [
            'id' => 'soporte',
            'icon' => 'fa-headset',
            'title' => 'Soporte',
            'items' => $items,
        ];
    }

    /**
     * Obtener navegación de Bodega
     */
    protected function getBodegaNav(): array
    {
        $items = [];

        if ($this->routeExists('manager.warehouse.index')) {
            $items[] = [
                'label' => 'Pisos',
                'url' => route('manager.warehouse.index'),
                'active' => str_contains(Route::currentRouteName(), 'warehouse'),
            ];
        }

        if ($this->routeExists('manager.warehouse.styles')) {
            $items[] = [
                'label' => 'Estilos de ubicaciones',
                'url' => route('manager.warehouse.styles'),
                'active' => str_contains(Route::currentRouteName(), 'warehouse.styles'),
            ];
        }

        return [
            'id' => 'bodega',
            'icon' => 'fa-warehouse',
            'title' => 'Bodega',
            'items' => $items,
        ];
    }

    /**
     * Obtener navegación de Configuración
     */
    protected function getConfiguracionNav(): array
    {
        $items = [];

        // General
        $routes = [
            ['name' => 'manager.settings', 'label' => 'Principal', 'key' => 'settings'],
            ['name' => 'manager.categories', 'label' => 'Categorías', 'key' => 'categories'],
            ['name' => 'manager.settings.search.index', 'label' => 'Búsqueda', 'key' => 'settings.search'],
            ['name' => 'manager.settings.localization.index', 'label' => 'Localización', 'key' => 'settings.localization'],
            ['name' => 'manager.settings.translations.index', 'label' => 'Traducciones', 'key' => 'settings.translations'],
            ['name' => 'manager.media.index', 'label' => 'Gestor de Medios', 'key' => 'media.index'],
        ];

        foreach ($routes as $route) {
            $url = $this->safeRoute($route['name']);
            if ($url) {
                $items[] = ['label' => $route['label'], 'url' => $url, 'active' => str_contains(Route::currentRouteName(), $route['key'])];
            }
        }

        // Helpdesk - Solo si el módulo está disponible
        $helpdesk_routes = [
            ['name' => 'manager.helpdesk.settings.livechat', 'label' => 'Live chat', 'key' => 'helpdesk.settings.livechat'],
            ['name' => 'manager.helpdesk.settings.ai', 'label' => 'Inteligencia artificial', 'key' => 'helpdesk.settings.ai'],
        ];

        foreach ($helpdesk_routes as $route) {
            $url = $this->safeRoute($route['name']);
            if ($url) {
                $items[] = ['label' => $route['label'], 'url' => $url, 'active' => str_contains(Route::currentRouteName(), $route['key'])];
            }
        }

        // Email/Comunicaciones
        $email_routes = [
            ['name' => 'settings.email.index', 'label' => 'Email/SMTP', 'key' => 'settings.email'],
            ['name' => 'manager.settings.mailers.templates.index', 'label' => 'Plantillas de correo', 'key' => 'settings.mailers'],
            ['name' => 'manager.settings.mailers.components.index', 'label' => 'Componentes de correo', 'key' => false],
            ['name' => 'manager.settings.mailers.variables.index', 'label' => 'Variables de correo', 'key' => false],
            ['name' => 'manager.settings.mailers.endpoints.index', 'label' => 'Email Endpoints', 'key' => false],
        ];

        foreach ($email_routes as $route) {
            $url = $this->safeRoute($route['name']);
            if ($url) {
                $items[] = ['label' => $route['label'], 'url' => $url, 'active' => $route['key'] ? str_contains(Route::currentRouteName(), $route['key']) : false];
            }
        }

        // Webhooks
        $url = $this->safeRoute('manager.settings.webhooks.integrations.index');
        if ($url) {
            $items[] = ['label' => 'Webhooks', 'url' => $url, 'active' => str_contains(Route::currentRouteName(), 'webhooks')];
        }

        // Almacenamiento
        $url = $this->safeRoute('manager.settings.storage');
        if ($url) {
            $items[] = ['label' => 'Almacenamiento', 'url' => $url, 'active' => Route::currentRouteName() === 'manager.settings.storage'];
        }

        return [
            'id' => 'configuracion',
            'icon' => 'fa-sliders',
            'title' => 'Configuración',
            'items' => $items,
        ];
    }

    /**
     * Obtener navegación de Acceso & Permisos
     */
    protected function getAccesoNav(): array
    {
        if (! auth()->user() || ! auth()->user()->can('role:view')) {
            return ['id' => 'acceso', 'icon' => 'fa-shield-halved', 'title' => 'Acceso', 'items' => []];
        }

        $items = [];

        if (auth()->user()->can('role:view')) {
            $url = $this->safeRoute('manager.roles');
            if ($url) {
                $items[] = ['label' => 'Roles', 'url' => $url, 'active' => str_contains(Route::currentRouteName(), 'roles')];
            }
        }

        $url = $this->safeRoute('manager.permissions');
        if ($url) {
            $items[] = ['label' => 'Permisos', 'url' => $url, 'active' => str_contains(Route::currentRouteName(), 'permissions')];
        }

        return [
            'id' => 'acceso',
            'icon' => 'fa-shield-halved',
            'title' => 'Acceso',
            'items' => $items,
        ];
    }

    /**
     * Verificar si una ruta existe
     */
    protected function routeExists(string $routeName): bool
    {
        return Route::has($routeName);
    }

    /**
     * Obtener toda la navegación
     */
    public function getNavigation(): array
    {
        return array_filter($this->navigation, fn ($section) => ! empty($section['items']));
    }

    /**
     * Obtener navegación por ID
     */
    public function getNavigationSection(string $id): ?array
    {
        return $this->navigation[$id] ?? null;
    }

    /**
     * Obtener ícono de sección
     */
    public function getIcon(string $sectionId): string
    {
        return $this->navigation[$sectionId]['icon'] ?? 'fa-cog';
    }
}
