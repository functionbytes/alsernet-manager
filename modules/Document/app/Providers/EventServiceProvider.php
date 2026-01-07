<?php

namespace Modules\Document\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Event listeners registry
     *
     * NOTA: Actualmente no hay eventos implementados en este módulo.
     * Las carpetas app/Events y app/Listeners no existen.
     * Si necesitas agregar eventos, crear primero las clases correspondientes.
     */
    protected $listen = [
        // TODO: Implementar eventos cuando sea necesario
        // Ejemplo:
        // DocumentCreated::class => [
        //     SendDocumentUploadNotification::class,
        // ],
    ];

    public function boot(): void
    {
        parent::boot();

    }

    private function registerBackupEventHandlers(): void {}

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
