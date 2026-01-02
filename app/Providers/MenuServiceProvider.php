<?php

namespace App\Providers;

use App\Services\NavService;
use Illuminate\Support\ServiceProvider;

/**
 * MenuServiceProvider - Registra todos los menús del panel administrativo
 *
 * Este provider se encarga de coordinar el registro de items de menú
 * desde todos los módulos y hacerlos disponibles en las vistas.
 */
class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * Se ejecuta después de que todos los providers hayan sido registrados,
     * permitiendo que cada módulo haya tenido oportunidad de registrar sus menús.
     */
    public function boot(): void
    {
        // Registrar menús del core/sistema principal
        $this->registerCoreMenus();

        // Hacer el servicio disponible en las vistas
        $this->shareWithViews();
    }

    /**
     * Registrar menús del core del sistema (solo core, no módulos)
     *
     * Los módulos deben registrar sus menús en su propio ServiceProvider
     */
    private function registerCoreMenus(): void
    {
        // Menú de Configuración (CORE)
        NavService::registerMiniItem('settings', [
            'icon' => 'fa-sliders',
            'tooltip' => 'Configuración',
            'sidebar_id' => 'settings',
            'order' => 10,
        ]);

        NavService::registerSidebar('settings', [
            'title' => 'Configuración',
            'items' => [
                ['label' => 'Principal', 'route' => 'manager.settings', 'icon' => 'fa-cog'],
                ['label' => 'Módulos', 'route' => 'modules.index', 'icon' => 'fa-cube'],
                ['label' => 'Categorías', 'route' => 'manager.settings.categories.index'],
                ['label' => 'Búsqueda', 'route' => 'manager.settings.search.index'],
                ['label' => 'Localización', 'route' => 'manager.settings.localization.index'],
                ['label' => 'Traducciones', 'route' => 'manager.settings.translations.index'],
                ['label' => 'Carga de archivos', 'route' => 'manager.settings.uploading.index'],
                ['label' => 'Email/SMTP', 'route' => 'manager.settings.email.index'],
                ['label' => 'Almacenamiento', 'route' => 'manager.settings.storage'],
            ],
        ]);

        // NOTA: Los menús de módulos se registran en sus propios ServiceProviders
        // Ver: modules/*/app/Providers/*ServiceProvider.php
    }

    /**
     * Compartir el servicio con las vistas
     */
    private function shareWithViews(): void
    {
        \View::share('navService', NavService::class);
    }
}
