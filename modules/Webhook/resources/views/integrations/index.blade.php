@extends('layouts.theme')

@section('title', 'Integraciones Webhook')

@section('content')

    @include('managers.includes.card', ['title' => 'Integraciones Webhook'])

    <div class="widget-content searchable-container list">

        @include('managers.components.alerts')

        <div class="card">
            <div class="card-header p-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Integraciones Webhook</h5>
                        <p class="small mb-0 text-muted">Gestiona las integraciones que pueden recibir y enviar webhooks</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if(request('search') || request('status') || request('plan'))
                            <a href="{{ route('manager.settings.webhooks.integrations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Limpiar filtros
                            </a>
                        @endif
                        <a href="{{ route('manager.settings.webhooks.integrations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Nueva integración
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.settings.webhooks.integrations.index') }}">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="search" name="search" class="form-control"
                                       placeholder="Buscar por nombre o UID..."
                                       value="{{ $search }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">Todos los estados</option>
                                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Activo</option>
                                <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspendido</option>
                                <option value="disabled" {{ $status === 'disabled' ? 'selected' : '' }}>Deshabilitado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="plan">
                                <option value="">Todos los planes</option>
                                <option value="free" {{ $plan === 'free' ? 'selected' : '' }}>Free</option>
                                <option value="pro" {{ $plan === 'pro' ? 'selected' : '' }}>Pro</option>
                                <option value="enterprise" {{ $plan === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body">
                @if($integrations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Integración</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Plan</th>
                                    <th class="text-center">Eventos</th>
                                    <th class="text-center">Suscripciones</th>
                                    <th class="text-center">Entregas</th>
                                    <th>Creada</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($integrations as $integration)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $integration->name }}</strong>
                                                <br>
                                                <small class="text-muted font-monospace">{{ $integration->uid }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($integration->status === 'active')
                                                <span class="badge bg-success">Activo</span>
                                            @elseif($integration->status === 'suspended')
                                                <span class="badge bg-warning">Suspendido</span>
                                            @else
                                                <span class="badge bg-secondary">Deshabilitado</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ ucfirst($integration->plan) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $integration->events_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $integration->subscriptions_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $integration->deliveries_count }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $integration->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('manager.settings.webhooks.integrations.edit', $integration) }}"
                                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('manager.settings.webhooks.integrations.api-keys', $integration) }}"
                                                   class="btn btn-sm btn-outline-secondary" title="API Keys">
                                                    <i class="fas fa-key"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger delete-integration"
                                                        data-id="{{ $integration->id }}"
                                                        data-name="{{ $integration->name }}"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $integrations->withQueryString()->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No se encontraron integraciones.
                        <a href="{{ route('manager.settings.webhooks.integrations.create') }}">Crear la primera integración</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-integration').forEach(btn => {
        btn.addEventListener('click', function() {
            const integrationId = this.dataset.id;
            const integrationName = this.dataset.name;

            Swal.fire({
                title: '¿Eliminar integración?',
                text: `Se eliminará "${integrationName}" y todos sus datos asociados.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/manager/settings/webhooks/integrations/${integrationId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Eliminado!', data.message, 'success')
                                .then(() => window.location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Error al eliminar la integración', 'error');
                    });
                }
            });
        });
    });
});
</script>
@endpush
