@extends('layouts.theme')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Webhooks</h1>
            <p class="text-muted small mb-0">Gestiona integraciones y entregas de webhooks</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('manager.webhooks.index') }}" class="row gap-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Buscar integración..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendido</option>
                        <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Deshabilitado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Integrations Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nombre</th>
                        <th>Estado</th>
                        <th>Plan</th>
                        <th>Suscripciones</th>
                        <th>Límite diario</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($integrations as $integration)
                        <tr>
                            <td class="ps-3 fw-medium">
                                <a href="{{ route('manager.webhooks.show', $integration->uid) }}" class="text-decoration-none">
                                    {{ $integration->name }}
                                </a>
                            </td>
                            <td>
                                @if($integration->isActive())
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="fas fa-check-circle me-1"></i>Activo
                                    </span>
                                @elseif($integration->isSuspended())
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="fas fa-pause-circle me-1"></i>Suspendido
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">
                                        <i class="fas fa-times-circle me-1"></i>Deshabilitado
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info">{{ ucfirst($integration->plan) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $integration->subscriptions_count ?? $integration->subscriptions->count() }} suscripciones
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ number_format($integration->daily_limit) }} req/día</small>
                            </td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('manager.webhooks.show', $integration->uid) }}"
                                   class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-3 d-block opacity-50"></i>
                                    <p class="mb-0">No hay integraciones registradas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($integrations->hasPages())
            <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Mostrando {{ $integrations->from() ?? 0 }}-{{ $integrations->to() ?? 0 }} de {{ $integrations->total() }}
                </small>
                {{ $integrations->links() }}
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        font-weight: 500;
    }
</style>
@endpush
@endsection
