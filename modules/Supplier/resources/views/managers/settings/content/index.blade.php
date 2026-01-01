@extends('layouts.theme')

@section('title', 'Revisión de Contenido')

@section('content')

    @include('managers.components.card', ['title' => 'Revisión de Contenido'])

    <div class="widget-content searchable-container list">

        @include('managers.components.alerts')

        <div class="card">
            <!-- Header Section -->
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Contenido de IA generado</h5>
                        <p class="small mb-0 text-muted">Revisa y aprueba el contenido generado por IA</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if(request('search') || request('status') || request('supplier_id'))
                            <a href="{{ route('manager.settings.suppliers.content.index') }}" class="btn btn-secondary">
                                Limpiar filtros
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-primary mb-2">Total</h6>
                                        <h4 class="mb-1 fw-bold">{{ $stats['total'] }}</h4>
                                        <small class="text-muted">Contenidos generados</small>
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
                                        <h6 class="card-title text-warning mb-2">Pendiente</h6>
                                        <h4 class="mb-1 fw-bold">{{ $stats['pending'] }}</h4>
                                        <small class="text-muted">Requiere revisión</small>
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
                                        <h6 class="card-title text-success mb-2">Aprobado</h6>
                                        <h4 class="mb-1 fw-bold">{{ $stats['approved'] }}</h4>
                                        <small class="text-muted">Contenido validado</small>
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
                                        <h6 class="card-title text-danger mb-2">Rechazado</h6>
                                        <h4 class="mb-1 fw-bold">{{ $stats['rejected'] }}</h4>
                                        <small class="text-muted">Contenido descartado</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters Section -->
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.settings.suppliers.content.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="search" name="search" class="form-control" placeholder="Buscar contenido..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Proveedor</label>
                            <select class="form-select" name="supplier_id">
                                <option value="">Todos los proveedores</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Estado</label>
                            <select class="form-select" name="status">
                                <option value="">Todos los estados</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobado</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Content List -->
            <div class="card-body">
                @if($contents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                            <tr>
                                <th width="25%">Producto</th>
                                <th width="15%">Proveedor</th>
                                <th width="15%">Tipo de contenido</th>
                                <th width="10%">Prompt</th>
                                <th width="10%" class="text-center">Estado</th>
                                <th width="15%">Fecha</th>
                                <th width="10%" class="text-center">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($contents as $content)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $content->generated_name ?? $content->model_id ?? 'Sin nombre' }}</strong>
                                            @if($content->erp_reference)
                                                <br><small class="text-muted">Ref: {{ $content->erp_reference }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($content->supplier)
                                            <small class="text-muted">{{ $content->supplier->name }}</small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            @php
                                                $types = [];
                                                if($content->generated_name) $types[] = 'Nombre';
                                                if($content->short_description) $types[] = 'Descripción';
                                                if($content->seo_title) $types[] = 'SEO';
                                            @endphp
                                            {{ !empty($types) ? implode(', ', $types) : 'Completo' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($content->prompt)
                                            <small class="text-muted">{{ $content->prompt->label }}</small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @switch($content->status)
                                            @case('pending')
                                                <span class="badge bg-warning">Pendiente</span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-success">Aprobado</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">Rechazado</span>
                                                @break
                                            @case('published')
                                                <span class="badge bg-info">Publicado</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($content->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $content->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-duotone fa-solid fa-ellipsis"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('manager.settings.suppliers.content.show', $content->uid) }}">
                                                        <i class="fas fa-eye me-2"></i> Ver detalle
                                                    </a>
                                                </li>
                                                @if($content->status === 'pending')
                                                    <li>
                                                        <form method="POST" action="{{ route('manager.settings.suppliers.content.action', $content->uid) }}" class="approve-form">
                                                            @csrf
                                                            <input type="hidden" name="action" value="approve">
                                                            <button type="submit" class="dropdown-item text-success">
                                                                <i class="fas fa-check me-2"></i> Aprobar
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item text-danger reject-btn"
                                                                data-uid="{{ $content->uid }}">
                                                            <i class="fas fa-times me-2"></i> Rechazar
                                                        </button>
                                                    </li>
                                                @endif
                                                @if($content->status === 'approved')
                                                    <li>
                                                        <form method="POST" action="{{ route('manager.settings.suppliers.content.publish', $content->uid) }}" class="publish-form">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-info">
                                                                <i class="fas fa-paper-plane me-2"></i> Publicar
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('manager.settings.suppliers.content.action', $content->uid) }}" class="regenerate-form">
                                                        @csrf
                                                        <input type="hidden" name="action" value="regenerate">
                                                        <button type="submit" class="dropdown-item text-warning">
                                                            <i class="fas fa-redo me-2"></i> Regenerar
                                                        </button>
                                                    </form>
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
                                <i class="fas fa-magic fs-7"></i>
                            </div>
                            <h6 class="mb-1">No hay contenido para mostrar</h6>
                            <p class="text-muted mb-3">
                                @if(request('search') || request('status') || request('supplier_id'))
                                    No se encontraron resultados con los filtros aplicados
                                @else
                                    Aún no se ha generado contenido con IA
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($contents->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando <strong>{{ $contents->firstItem() }}</strong> a <strong>{{ $contents->lastItem() }}</strong>
                            de <strong>{{ $contents->total() }}</strong> contenidos
                        </div>
                        <nav aria-label="Page navigation">
                            {{ $contents->links() }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Approve form confirmation
    $('.approve-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: '¿Aprobar contenido?',
            text: 'El contenido será marcado como aprobado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Reject button - open modal for reason
    $('.reject-btn').on('click', function() {
        const uid = $(this).data('uid');

        Swal.fire({
            title: '¿Rechazar contenido?',
            input: 'textarea',
            inputLabel: 'Motivo del rechazo',
            inputPlaceholder: 'Escribe el motivo...',
            inputAttributes: {
                'aria-label': 'Escribe el motivo del rechazo'
            },
            showCancelButton: true,
            confirmButtonText: 'Rechazar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes escribir un motivo'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form and submit
                const form = $('<form>', {
                    'method': 'POST',
                    'action': '{{ route("manager.settings.suppliers.content.action", ":uid") }}'.replace(':uid', uid)
                });

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': '{{ csrf_token() }}'
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'action',
                    'value': 'reject'
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'reason',
                    'value': result.value
                }));

                $('body').append(form);
                form.submit();
            }
        });
    });

    // Publish form confirmation
    $('.publish-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: '¿Publicar contenido?',
            text: 'El contenido se publicará en la tienda.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, publicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Regenerate form confirmation
    $('.regenerate-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: '¿Regenerar contenido?',
            text: 'Se generará nuevo contenido con IA.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Sí, regenerar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    @if (session('success'))
        toastr.success('{{ session('success') }}', 'Éxito');
    @endif

    @if (session('error'))
        toastr.error('{{ session('error') }}', 'Error');
    @endif
});
</script>
@endpush
