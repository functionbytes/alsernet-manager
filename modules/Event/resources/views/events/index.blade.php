@extends('layouts.theme')

@section('content')
    @include('core::components.card', ['title' => 'Gestión de eventos'])

    <div class="widget-content searchable-container list">

        @include('core::components.alerts')

        <div class="card">
            {{-- Header Section --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Eventos</h5>
                        <p class="small mb-0 text-muted">Administra todos los eventos que se sincronizarán con PrestaShop</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if($searchKey || $available !== null)
                            <a href="{{ route('manager.events.index') }}" class="btn btn-secondary">
                                Limpiar búsqueda
                            </a>
                        @endif
                        <a href="{{ route('manager.events.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Crear evento
                        </a>
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-primary mb-2">Total</h6>
                                        <h4 class="mb-1 fw-bold">{{ $events->total() }}</h4>
                                        <small class="text-muted">Eventos creados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-success mb-2">Públicos</h6>
                                        <h4 class="mb-1 fw-bold">{{ $events->where('available', true)->count() }}</h4>
                                        <small class="text-muted">Eventos visibles</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-danger mb-2">Ocultos</h6>
                                        <h4 class="mb-1 fw-bold">{{ $events->where('available', false)->count() }}</h4>
                                        <small class="text-muted">Eventos ocultos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-info mb-2">Destacados</h6>
                                        <h4 class="mb-1 fw-bold">{{ $events->where('featured', true)->count() }}</h4>
                                        <small class="text-muted">Con destaque</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search Section --}}
            <div class="card-body border-bottom">
                <form action="{{ route('manager.events.index') }}" method="GET">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="search" name="search" class="form-control"
                                       placeholder="Buscar por título..." value="{{ $searchKey }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="available" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="1" @selected($available == 1)>Públicos</option>
                                <option value="0" @selected($available == 0)>Ocultos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($events->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="25%">Evento</th>
                                    <th width="15%">Inicio</th>
                                    <th width="15%">Finalización</th>
                                    <th width="15%">Estado</th>
                                    <th width="15%">Destacado</th>
                                    <th width="15%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($events as $event)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar avatar-sm" style="background-color: {{ $event->color_flag ?? '#90bb13' }}; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">
                                                    {{ substr($event->title, 0, 1) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('manager.events.view', $event->uid) }}" class="fw-bold text-decoration-none">
                                                        {{ Str::limit($event->title, 40) }}
                                                    </a>
                                                    <small class="text-muted d-block">{{ $event->uid }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $event->start_at?->format('d/m/Y H:i') ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $event->end_at?->format('d/m/Y H:i') ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @if($event->available)
                                                <span class="badge bg-success-subtle text-success">Público</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Oculto</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($event->featured)
                                                <span class="badge bg-info-subtle text-info">Destacado</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Normal</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a href="{{ route('manager.events.view', $event->uid) }}" class="dropdown-item">
                                                            <i class="fas fa-eye me-2"></i>Ver detalles
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('manager.events.edit', $event->uid) }}" class="dropdown-item">
                                                            <i class="fas fa-edit me-2"></i>Editar evento
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item text-danger delete-event-btn"
                                                                data-event-uid="{{ $event->uid }}"
                                                                data-event-title="{{ $event->title }}">
                                                            <i class="fas fa-trash me-2"></i>Eliminar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <div class="round-48 rounded-circle bg-light-subtle text-muted mb-3 d-flex align-items-center justify-content-center">
                                <i class="fas fa-calendar fs-7"></i>
                            </div>
                            <h6 class="mb-1">No hay eventos para mostrar</h6>
                            <p class="text-muted mb-3">
                                @if($searchKey || $available !== null)
                                    No se encontraron resultados con los filtros aplicados
                                @else
                                    Comienza creando tu primer evento
                                @endif
                            </p>
                            @if(!($searchKey || $available !== null))
                                <a href="{{ route('manager.events.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-2"></i>Crear evento
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Pagination --}}
            @if($events->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando <strong>{{ $events->firstItem() }}</strong> a <strong>{{ $events->lastItem() }}</strong>
                            de <strong>{{ $events->total() }}</strong> eventos
                        </div>
                        <nav aria-label="Page navigation">
                            {{ $events->links() }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Handle delete event
            $(document).on('click', '.delete-event-btn', function(e) {
                e.preventDefault();

                const uid = $(this).data('event-uid');
                const title = $(this).data('event-title');

                if (confirm('¿Estás seguro de que deseas eliminar el evento "' + title + '"?\n\nEsta acción no se puede deshacer.')) {
                    $.ajax({
                        url: '{{ url("/manager/events") }}/' + uid,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message || 'Evento eliminado correctamente', 'Éxito', {
                                    positionClass: 'toast-bottom-right'
                                });
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                toastr.error(response.message || 'Error al eliminar el evento', 'Error', {
                                    positionClass: 'toast-bottom-right'
                                });
                            }
                        },
                        error: function(xhr) {
                            let message = 'Error al eliminar el evento';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            toastr.error(message, 'Error', { positionClass: 'toast-bottom-right' });
                        }
                    });
                }
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush

<style>
    .stat-card {
        border: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .bg-light-secondary {
        background-color: #f8f9fa;
    }

    .bg-success-subtle {
        background-color: rgba(19, 198, 114, 0.1);
    }

    .bg-danger-subtle {
        background-color: rgba(250, 137, 107, 0.1);
    }

    .bg-info-subtle {
        background-color: rgba(33, 150, 243, 0.1);
    }

    .bg-secondary-subtle {
        background-color: rgba(108, 117, 125, 0.1);
    }
</style>
