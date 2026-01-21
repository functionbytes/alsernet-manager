<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="fas fa-sync-alt me-2" style="color: #90bb13;"></i>Sincronización en tiempo real
                </h4>
                <p class="text-muted small mb-0">Monitorea y controla todas las operaciones de sincronización</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="refreshStats">
                    <i class="fas fa-redo me-1"></i>Actualizar
                </button>
                <button type="button" class="btn btn-sm btn-success">
                    <i class="fas fa-play me-1"></i>Sincronizar todo
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Synced -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="overflow: hidden;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-2 fw-semibold">Total sincronizado</p>
                            <h3 class="fw-bold mb-0" style="color: #90bb13;">
                                {{ number_format($statistics['total_synced'] ?? 0) }}
                            </h3>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: rgba(144, 187, 19, 0.1);">
                            <i class="fas fa-check" style="color: #90bb13; font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="mt-3 small">
                        <span class="text-success"><i class="fas fa-arrow-up"></i> +12% esta semana</span>
                    </div>
                </div>
                <div class="progress" style="height: 3px; border-radius: 0;">
                    <div class="progress-bar" role="progressbar" style="width: 85%; background-color: #90bb13;"></div>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="overflow: hidden;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-2 fw-semibold">Pendiente</p>
                            <h3 class="fw-bold mb-0" style="color: #FEC90F;">
                                {{ number_format($statistics['pending'] ?? 0) }}
                            </h3>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: rgba(254, 201, 15, 0.1);">
                            <i class="fas fa-clock" style="color: #FEC90F; font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="mt-3 small">
                        <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Requiere atención</span>
                    </div>
                </div>
                <div class="progress" style="height: 3px; border-radius: 0;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 45%;"></div>
                </div>
            </div>
        </div>

        <!-- Failed -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="overflow: hidden;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-2 fw-semibold">Fallidas</p>
                            <h3 class="fw-bold mb-0" style="color: #FA896B;">
                                {{ number_format($statistics['failed'] ?? 0) }}
                            </h3>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: rgba(250, 137, 107, 0.1);">
                            <i class="fas fa-times-circle" style="color: #FA896B; font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="mt-3 small">
                        <a href="#" class="text-danger"><i class="fas fa-arrow-right"></i> Ver detalles</a>
                    </div>
                </div>
                <div class="progress" style="height: 3px; border-radius: 0;">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: 15%;"></div>
                </div>
            </div>
        </div>

        <!-- Last Sync -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="overflow: hidden;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-2 fw-semibold">Última sincronización</p>
                            <h6 class="fw-bold mb-0">
                                {{ $statistics['last_sync'] ? $statistics['last_sync']->diffForHumans() : 'Nunca' }}
                            </h6>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: rgba(108, 117, 125, 0.1);">
                            <i class="fas fa-history" style="color: #6c757d; font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="mt-3 small text-muted">
                        <i class="fas fa-info-circle"></i> Sincronización automática
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Types Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h6 class="fw-bold mb-3 border-bottom pb-2">
                <i class="fas fa-th me-2" style="color: #90bb13;"></i>Tipos de sincronización
            </h6>
        </div>

        <!-- Product Sync Card -->
        <livewire:supplier-sync.sync-type-card
            syncType="product"
            icon="fas fa-cube"
        />

        <!-- Category Sync Card -->
        <livewire:supplier-sync.sync-type-card
            syncType="category"
            icon="fas fa-sitemap"
        />

        <!-- Price Sync Card -->
        <livewire:supplier-sync.sync-type-card
            syncType="price"
            icon="fas fa-tag"
        />

        <!-- Provider Sync Card -->
        <livewire:supplier-sync.sync-type-card
            syncType="provider"
            icon="fas fa-truck"
        />
    </div>

    <!-- Active Syncs Status -->
    <div class="row mb-4" wire:ignore>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-tasks me-2" style="color: #90bb13;"></i>Estado de sincronizaciones activas
                    </h6>
                </div>
                <div class="card-body p-0">
                    <livewire:supplier-sync.sync-status-table />
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-list me-2" style="color: #90bb13;"></i>Actividad reciente
                    </h6>
                </div>
                <div class="card-body p-0">
                    <livewire:supplier-sync.sync-activity-log />
                </div>
            </div>
        </div>
    </div>
</div>
