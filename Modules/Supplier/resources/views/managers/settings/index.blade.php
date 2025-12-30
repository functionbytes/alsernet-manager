@extends('layouts.managers')

@section('title', 'Proveedores')

@section('content')

    @include('managers.components.card', ['title' => 'Proveedores'])

    <div class="widget-content searchable-container list">

        @include('managers.components.alerts')

        <!-- Suppliers Card -->
        <div class="card">
            <!-- Header Section -->
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Proveedores</h5>
                        <p class="small mb-0 text-muted">Gestiona los proveedores y sus fuentes de productos</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if(request('search') || request('is_active'))
                            <a href="{{ route('manager.settings.suppliers.index') }}" class="btn btn-secondary">
                                Limpiar búsqueda
                            </a>
                        @endif
                        <a href="{{ route('manager.settings.suppliers.create') }}" class="btn btn-primary">
                            Nuevo proveedor
                        </a>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.settings.suppliers.index') }}">
                    <div class="row align-items-center g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa fa-magnifying-glass"></i>
                                </span>
                                <input type="search" name="search" class="form-control"
                                       placeholder="Buscar por código o nombre..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select select2" name="is_active" data-minimum-results-for-search="Infinity">
                                <option value="">Todos los estados</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Suppliers List -->
            <div class="card-body">
                @if($suppliers->count() > 0)
                    <div class="alert alert-info mb-3">
                        <i class="fa fa-circle-info me-2"></i>
                        Los proveedores definen las fuentes de productos que se sincronizarán con el sistema
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Código</th>
                                    <th>Sitio web</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Prioridad</th>
                                    <th class="text-center">Fuentes</th>
                                    <th>Última actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suppliers as $supplier)
                                    <tr>

                                        <td>
                                            <div>
                                                {{ $supplier->name }}
                                                @if($supplier->description)
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($supplier->description, 60) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <code class="bg-light px-2 py-1 rounded">{{ strtoupper($supplier->code) }}</code>
                                        </td>

                                        <td>
                                            @if($supplier->website)
                                                <a href="{{ $supplier->website }}" target="_blank" class="text-primary">
                                                    {{ Str::limit($supplier->website, 30) }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($supplier->is_active)
                                                <span class="badge bg-success-subtle text-success">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light-secondary text-info ">
                                                {{ $supplier->priority }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $supplier->sources->count() }} {{ $supplier->sources->count() == 1 ? 'fuente' : 'fuentes' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $supplier->updated_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.settings.suppliers.edit', $supplier->uid) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.settings.suppliers.sources.index', $supplier->uid) }}">
                                                            Fuentes
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item confirm-delete" data-href="{{ route('manager.settings.suppliers.destroy', $supplier->uid) }}">
                                                            Eliminar
                                                        </a>
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
                                <i class="fa fa-inbox fs-7"></i>
                            </div>
                            <h6 class="mb-1">No hay proveedores para mostrar</h6>
                            <p class="text-muted mb-3">
                                @if(request('search'))
                                    No se encontraron resultados para "{{ request('search') }}"
                                @else
                                    Crea tu primer proveedor para comenzar
                                @endif
                            </p>
                            @if(!request('search'))
                                <a href="{{ route('manager.settings.suppliers.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus"></i> Crear primer proveedor
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($suppliers->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando <strong>{{ $suppliers->firstItem() }}</strong> a <strong>{{ $suppliers->lastItem() }}</strong>
                            de <strong>{{ $suppliers->total() }}</strong> proveedores
                        </div>
                        <nav aria-label="Page navigation">
                            {{ $suppliers->appends(request()->input())->links() }}
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
    // Initialize Select2
    $('.select2').select2({
        allowClear: false,
        minimumResultsForSearch: Infinity
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
