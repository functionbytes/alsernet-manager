@extends('layouts.theme')

@section('title', 'Panel de Sincronización')

@push('styles')
<style>
    .sync-card {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }
    .sync-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    .sync-card.sync-success { border-left-color: #13C672; }
    .sync-card.sync-warning { border-left-color: #FEC90F; }
    .sync-card.sync-danger { border-left-color: #FA896B; }
    .sync-card.sync-info { border-left-color: #539BFF; }

    .sync-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .sync-badge.badge-success { background: #13C67220; color: #0a7a4a; }
    .sync-badge.badge-warning { background: #FEC90F20; color: #8b6407; }
    .sync-badge.badge-danger { background: #FA896B20; color: #9d3a23; }
    .sync-badge.badge-info { background: #539BFF20; color: #1e4a96; }
    .sync-badge.badge-pending { background: #6c757d20; color: #3d4349; }

    .progress-sync {
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
    }
    .progress-sync .progress-bar {
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .timeline-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        border-left: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .timeline-item:hover {
        background: #f8f9fa;
        border-left-color: #90bb13;
    }
    .timeline-icon {
        min-width: 40px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #f0f2f5;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
    @include('core::components.card', ['title' => 'Panel de Sincronización'])

    <div class="widget-content searchable-container">
        @include('core::components.alerts')

        <!-- Main Statistics -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Total Synced -->
                    <div class="col-md-3">
                        <div class="sync-card sync-success p-3">
                            <p class="stat-label mb-1">Sincronizados</p>
                            <p class="stat-value mb-0">{{ $stats['total_synced'] ?? 0 }}</p>
                            <small class="text-muted">Entidades activas</small>
                        </div>
                    </div>

                    <!-- Pending Sync -->
                    <div class="col-md-3">
                        <div class="sync-card sync-warning p-3">
                            <p class="stat-label mb-1">Pendientes</p>
                            <p class="stat-value mb-0">{{ $stats['pending_sync'] ?? 0 }}</p>
                            <small class="text-muted">Esperando sincronización</small>
                        </div>
                    </div>

                    <!-- Failed Sync -->
                    <div class="col-md-3">
                        <div class="sync-card sync-danger p-3">
                            <p class="stat-label mb-1">Errores</p>
                            <p class="stat-value mb-0">{{ $stats['failed_sync'] ?? 0 }}</p>
                            <small class="text-muted">Requieren atención</small>
                        </div>
                    </div>

                    <!-- Last Sync -->
                    <div class="col-md-3">
                        <div class="sync-card sync-info p-3">
                            <p class="stat-label mb-1">Última Sincronización</p>
                            <p class="stat-value mb-0" style="font-size: 1rem;">
                                @if($stats['last_sync'])
                                    {{ $stats['last_sync']->diffForHumans() }}
                                @else
                                    <span style="font-size: 0.875rem;">Sin registros</span>
                                @endif
                            </p>
                            <small class="text-muted">Hace poco</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Synchronization Types -->
        <div class="card">
            <div class="card-header p-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Tipos de Sincronización</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" id="syncAllBtn">
                            <i class="fas fa-sync me-1"></i> Sincronizar Todo
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <!-- Productos -->
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1 fw-bold">
                                        <i class="fas fa-box text-primary me-2"></i>Productos
                                    </h6>
                                    <small class="text-muted">Sincronización de productos</small>
                                </div>
                                <span class="sync-badge badge-{{ $stats['products_status'] ?? 'pending' }}">
                                    {{ ucfirst($stats['products_status'] ?? 'Pendiente') }}
                                </span>
                            </div>
                            <div class="progress-sync mb-3">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     style="width: {{ $stats['products_progress'] ?? 0 }}%"></div>
                            </div>
                            <div class="row text-center g-2 mb-3">
                                <div class="col">
                                    <p class="stat-label mb-1">Totales</p>
                                    <p class="stat-value" style="font-size: 1.25rem;">{{ $stats['products_total'] ?? 0 }}</p>
                                </div>
                                <div class="col">
                                    <p class="stat-label mb-1">Sincronizados</p>
                                    <p class="stat-value text-success" style="font-size: 1.25rem;">{{ $stats['products_synced'] ?? 0 }}</p>
                                </div>
                                <div class="col">
                                    <p class="stat-label mb-1">Errores</p>
                                    <p class="stat-value text-danger" style="font-size: 1.25rem;">{{ $stats['products_failed'] ?? 0 }}</p>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="{{ route('settings.suppliers.sync.products') }}" class="btn btn-sm btn-outline-primary">
                                    Ver Detalles
                                </a>
                                <button class="btn btn-sm btn-primary" onclick="syncProducts()">
                                    <i class="fas fa-sync me-1"></i>Sincronizar Ahora
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categorías -->
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1 fw-bold">
                                        <i class="fas fa-list text-info me-2"></i>Categorías
                                    </h6>
                                    <small class="text-muted">Sincronización de categorías</small>
                                </div>
                                <span class="sync-badge badge-{{ $stats['categories_status'] ?? 'pending' }}">
                                    {{ ucfirst($stats['categories_status'] ?? 'Pendiente') }}
                                </span>
                            </div>
                            <div class="progress-sync mb-3">
                                <div class="progress-bar bg-info" role="progressbar"
                                     style="width: {{ $stats['categories_progress'] ?? 0 }}%"></div>
                            </div>
                            <div class="row text-center g-2 mb-3">
                                <div class="col">
                                    <p class="stat-label mb-1">Totales</p>
                                    <p class="stat-value" style="font-size: 1.25rem;">{{ $stats['categories_total'] ?? 0 }}</p>
                                </div>
                                <div class="col">
                                    <p class="stat-label mb-1">Sincronizadas</p>
                                    <p class="stat-value text-success" style="font-size: 1.25rem;">{{ $stats['categories_synced'] ?? 0 }}</p>
                                </div>
                                <div class="col">
                                    <p class="stat-label mb-1">Errores</p>
                                    <p class="stat-value text-danger" style="font-size: 1.25rem;">{{ $stats['categories_failed'] ?? 0 }}</p>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="{{ route('settings.suppliers.sync.categories') }}" class="btn btn-sm btn-outline-info">
                                    Ver Detalles
                                </a>
                                <button class="btn btn-sm btn-info" onclick="syncCategories()">
                                    <i class="fas fa-sync me-1"></i>Sincronizar Ahora
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Precios -->
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1 fw-bold">
                                        <i class="fas fa-tag text-warning me-2"></i>Precios
                                    </h6>
                                    <small class="text-muted">Sincronización de precios</small>
                                </div>
                                <span class="sync-badge badge-{{ $stats['prices_status'] ?? 'pending' }}">
                                    {{ ucfirst($stats['prices_status'] ?? 'Pendiente') }}
                                </span>
                            </div>
                            <div class="progress-sync mb-3">
                                <div class="progress-bar bg-warning" role="progressbar"
                                     style="width: {{ $stats['prices_progress'] ?? 0 }}%"></div>
                            </div>
                            <div class="row text-center g-2 mb-3">
                                <div class="col">
                                    <p class="stat-label mb-1">Totales</p>
                                    <p class="stat-value" style="font-size: 1.25rem;">{{ $stats['prices_total'] ?? 0 }}</p>
                                </div>
                                <div class="col">
                                    <p class="stat-label mb-1">Sincronizados</p>
                                    <p class="stat-value text-success" style="font-size: 1.25rem;">{{ $stats['prices_synced'] ?? 0 }}</p>
                                </div>
                                <div class="col">
                                    <p class="stat-label mb-1">Errores</p>
                                    <p class="stat-value text-danger" style="font-size: 1.25rem;">{{ $stats['prices_failed'] ?? 0 }}</p>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="{{ route('settings.suppliers.sync.prices') }}" class="btn btn-sm btn-outline-warning">
                                    Ver Detalles
                                </a>
                                <button class="btn btn-sm btn-warning" onclick="syncPrices()">
                                    <i class="fas fa-sync me-1"></i>Sincronizar Ahora
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Proveedores/ERP -->
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1 fw-bold">
                                        <i class="fas fa-truck text-success me-2"></i>Proveedores/ERP
                                    </h6>
                                    <small class="text-muted">Sincronización con ERP</small>
                                </div>
                                <span class="sync-badge badge-{{ $stats['providers_status'] ?? 'pending' }}">
                                    {{ ucfirst($stats['providers_status'] ?? 'Pendiente') }}
                                </span>
                            </div>
                            <div class="progress-sync mb-3">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: {{ $stats['providers_progress'] ?? 0 }}%"></div>
                            </div>
                            <div class="row text-center g-2 mb-3">
                                <div class="col">
                                    <p class="stat-label mb-1">Totales</p>
                                    <p class="stat-value" style="font-size: 1.25rem;">{{ $stats['providers_total'] ?? 0 }}</p>
                                </div>
                                <div class="col">
                                    <p class="stat-label mb-1">Sincronizados</p>
                                    <p class="stat-value text-success" style="font-size: 1.25rem;">{{ $stats['providers_synced'] ?? 0 }}</p>
                                </div>
                                <div class="col">
                                    <p class="stat-label mb-1">Errores</p>
                                    <p class="stat-value text-danger" style="font-size: 1.25rem;">{{ $stats['providers_failed'] ?? 0 }}</p>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="{{ route('settings.suppliers.sync.providers') }}" class="btn btn-sm btn-outline-success">
                                    Ver Detalles
                                </a>
                                <button class="btn btn-sm btn-success" onclick="syncProviders()">
                                    <i class="fas fa-sync me-1"></i>Sincronizar Ahora
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mt-3">
            <div class="card-header p-4 border-bottom">
                <h5 class="mb-0 fw-bold">Actividad Reciente</h5>
            </div>
            <div class="card-body">
                @forelse($recentActivity as $activity)
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="fas fa-{{ $activity->type === 'success' ? 'check' : ($activity->type === 'error' ? 'times' : 'hourglass') }}
                                    text-{{ $activity->type === 'success' ? 'success' : ($activity->type === 'error' ? 'danger' : 'warning') }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-1">
                                <strong>{{ ucfirst($activity->entity_type) }}</strong> - {{ $activity->message }}
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>{{ $activity->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <span class="badge bg-{{ $activity->type === 'success' ? 'success' : ($activity->type === 'error' ? 'danger' : 'warning') }}">
                            {{ ucfirst($activity->type) }}
                        </span>
                    </div>
                @empty
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No hay actividad reciente
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function syncProducts() {
    syncEntity('products', 'Productos');
}

function syncCategories() {
    syncEntity('categories', 'Categorías');
}

function syncPrices() {
    syncEntity('prices', 'Precios');
}

function syncProviders() {
    syncEntity('providers', 'Proveedores');
}

function syncEntity(entity, label) {
    Swal.fire({
        title: '¿Sincronizar ' + label + '?',
        text: 'Esto iniciará el proceso de sincronización para ' + label.toLowerCase(),
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, sincronizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            toastr.info('Iniciando sincronización...', 'Sincronización');
            // TODO: Implementar endpoint de sincronización
        }
    });
}

function syncAllEntities() {
    Swal.fire({
        title: '¿Sincronizar Todo?',
        text: 'Esto iniciará la sincronización de todos los tipos de entidades',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, sincronizar todo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            toastr.info('Iniciando sincronización completa...', 'Sincronización');
            // TODO: Implementar endpoint de sincronización completa
        }
    });
}

document.getElementById('syncAllBtn')?.addEventListener('click', syncAllEntities);
</script>
@endpush
