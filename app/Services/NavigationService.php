<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
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
     * Obtener navegación del Dashboard
     */
    protected function getDashboardNav(): array
    {
        return [
            'id' => 'dashboard',
            'icon' => 'fa-chart-line',
            'title' => 'Dashboard',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'url' => route('manager.dashboard'),
                    'active' => Route::currentRouteName() === 'manager.dashboard',
                ],
            ],
        ];
    }

    /**
     * Obtener navegación de E-commerce
     */
    protected function getEcommerceNav(): array
    {
        $items = [];

        // Suscripciones
        $items[] = [
            'label' => 'Suscriptiones',
            'url' => route('manager.subscribers'),
            'active' => str_contains(Route::currentRouteName(), 'subscribers'),
        ];
        $items[] = [
            'label' => 'Listas',
            'url' => route('manager.subscribers.lists'),
            'active' => str_contains(Route::currentRouteName(), 'subscribers.lists'),
        ];
        $items[] = [
            'label' => 'Estados',
            'url' => route('manager.subscribers.conditions'),
            'active' => str_contains(Route::currentRouteName(), 'subscribers.conditions'),
        ];

        // Productos
        $items[] = [
            'label' => 'Productos',
            'url' => route('manager.inventaries'),
            'active' => str_contains(Route::currentRouteName(), 'inventaries'),
        ];

        // Tiendas
        $items[] = [
            'label' => 'Tiendas',
            'url' => route('manager.shops'),
            'active' => str_contains(Route::currentRouteName(), 'shops'),
        ];

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
        $items[] = [
            'label' => 'Campañas',
            'url' => route('manager.campaigns'),
            'active' => str_contains(Route::currentRouteName(), 'campaigns'),
        ];
        $items[] = [
            'label' => 'Listas',
            'url' => route('manager.maillists'),
            'active' => str_contains(Route::currentRouteName(), 'maillists'),
        ];

        // Plantillas
        $items[] = [
            'label' => 'Plantillas campaña',
            'url' => route('manager.templates'),
            'active' => str_contains(Route::currentRouteName(), 'templates'),
        ];
        $items[] = [
            'label' => 'Plantilla correos',
            'url' => route('manager.layouts'),
            'active' => str_contains(Route::currentRouteName(), 'layouts'),
        ];

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
        return [
            'id' => 'usuarios',
            'icon' => 'fa-users',
            'title' => 'Usuarios',
            'items' => [
                [
                    'label' => 'Usuarios',
                    'url' => route('manager.users'),
                    'active' => str_contains(Route::currentRouteName(), 'users'),
                ],
            ],
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
        $items[] = ['label' => 'Principal', 'url' => route('manager.settings'), 'active' => Route::currentRouteName() === 'manager.settings'];
        $items[] = ['label' => 'Categorías', 'url' => route('manager.categories'), 'active' => str_contains(Route::currentRouteName(), 'categories')];
        $items[] = ['label' => 'Búsqueda', 'url' => route('manager.settings.search.index'), 'active' => str_contains(Route::currentRouteName(), 'settings.search')];
        $items[] = ['label' => 'Localización', 'url' => route('manager.settings.localization.index'), 'active' => str_contains(Route::currentRouteName(), 'settings.localization')];
        $items[] = ['label' => 'Traducciones', 'url' => route('manager.settings.translations.index'), 'active' => str_contains(Route::currentRouteName(), 'settings.translations')];
        $items[] = ['label' => 'Carga de archivos', 'url' => route('manager.settings.uploading.index'), 'active' => str_contains(Route::currentRouteName(), 'settings.uploading')];
        $items[] = ['label' => 'Gestor de Medios', 'url' => route('manager.media.index'), 'active' => str_contains(Route::currentRouteName(), 'media.index')];

        // Helpdesk - Solo si el módulo está disponible
        $items[] = ['label' => 'Live chat', 'url' => route('manager.helpdesk.settings.livechat'), 'active' => str_contains(Route::currentRouteName(), 'helpdesk.settings.livechat')];
        $items[] = ['label' => 'Inteligencia artificial', 'url' => route('manager.helpdesk.settings.ai'), 'active' => str_contains(Route::currentRouteName(), 'helpdesk.settings.ai')];

        // Email/Comunicaciones
        $items[] = ['label' => 'Email/SMTP', 'url' => route('manager.settings.email.index'), 'active' => str_contains(Route::currentRouteName(), 'settings.email')];
        $items[] = ['label' => 'Plantillas de correo', 'url' => route('manager.settings.mailers.templates.index'), 'active' => str_contains(Route::currentRouteName(), 'settings.mailers')];
        $items[] = ['label' => 'Componentes de correo', 'url' => route('manager.settings.mailers.components.index'), 'active' => false];
        $items[] = ['label' => 'Variables de correo', 'url' => route('manager.settings.mailers.variables.index'), 'active' => false];
        $items[] = ['label' => 'Email Endpoints', 'url' => route('manager.settings.mailers.endpoints.index'), 'active' => false];

        // Webhooks
        if ($this->routeExists('manager.settings.webhooks.integrations.index')) {
            $items[] = ['label' => 'Webhooks', 'url' => route('manager.settings.webhooks.integrations.index'), 'active' => str_contains(Route::currentRouteName(), 'webhooks')];
        }

        // Almacenamiento
        $items[] = ['label' => 'Almacenamiento', 'url' => route('manager.settings.storage'), 'active' => Route::currentRouteName() === 'manager.settings.storage'];

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
        if (!auth()->user()->can('role:view')) {
            return ['id' => 'acceso', 'icon' => 'fa-shield-halved', 'title' => 'Acceso', 'items' => []];
        }

        $items = [];

        if (auth()->user()->can('role:view')) {
            $items[] = ['label' => 'Roles', 'url' => route('manager.roles'), 'active' => str_contains(Route::currentRouteName(), 'roles')];
        }

        $items[] = ['label' => 'Permisos', 'url' => route('manager.permissions'), 'active' => str_contains(Route::currentRouteName(), 'permissions')];

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
        return array_filter($this->navigation, fn ($section) => !empty($section['items']));
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
