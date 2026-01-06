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

        NavService::registerSidebar('settings', [
            'title' => 'Configuración',
            'items' => [
                ['label' => 'Principal', 'route' => 'manager.backups'],
                ['label' => 'Categorías', 'route' => 'manager.backups.categories.index'],
                ['label' => 'Búsqueda', 'route' => 'manager.backups.search.index'],
                ['label' => 'Localización', 'route' => 'manager.backups.localization.index'],
                ['label' => 'Traducciones', 'route' => 'manager.backups.translations.index'],
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
