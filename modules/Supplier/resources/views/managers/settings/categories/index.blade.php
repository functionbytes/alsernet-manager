@extends('layouts.theme')

@section('content')
    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-0 fw-bold">Categorías asignadas: {{ $supplier->name }}</h5>
                            <p class="card-subtitle mb-0 mt-2">Gestiona las categorías disponibles para este proveedor</p>
                        </div>
                        <a href="{{ route('manager.settings.suppliers.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>

                    @include('managers.components.alerts')

                    <!-- Stats Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-primary mb-2">Total asignadas</h6>
                                    <h4 class="mb-1 fw-bold">{{ $stats['total_assigned'] }}</h4>
                                    <small class="text-muted">Categorías configuradas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-success mb-2">Activas</h6>
                                    <h4 class="mb-1 fw-bold">{{ $stats['active'] }}</h4>
                                    <small class="text-muted">Categorías habilitadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-warning mb-2">Inactivas</h6>
                                    <h4 class="mb-1 fw-bold">{{ $stats['inactive'] }}</h4>
                                    <small class="text-muted">Categorías deshabilitadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-info mb-2">Disponibles</h6>
                                    <h4 class="mb-1 fw-bold">{{ $availableCategories->count() }}</h4>
                                    <small class="text-muted">Para asignar</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add New Category Form -->
                    @if($availableCategories->count() > 0)
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-plus-circle me-2 text-success"></i>Asignar nueva categoría
                                </h6>
                                <form action="{{ route('manager.settings.suppliers.categories.store', $supplier->uid) }}" method="POST">
                                    @csrf
                                    <div class="row align-items-end">
                                        <div class="col-md-7">
                                            <label class="form-label fw-semibold">Categoría</label>
                                            <select class="form-select @error('category_id') is-invalid @enderror" name="category_id" required>
                                                <option value="">Seleccionar categoría...</option>
                                                @foreach($availableCategories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Prioridad</label>
                                            <input type="number" class="form-control @error('priority') is-invalid @enderror"
                                                   name="priority" value="0" min="0" max="100">
                                            @error('priority')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="fas fa-plus me-1"></i> Asignar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Assigned Categories Table -->
                    @if($assignedCategories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="35%">Categoría</th>
                                        <th width="15%">Prioridad</th>
                                        <th width="15%">Prompts</th>
                                        <th width="15%">Fecha asignación</th>
                                        <th width="10%" class="text-center">Estado</th>
                                        <th width="10%" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignedCategories as $category)
                                        <tr>
                                            <td>
                                                <strong>{{ $category->title }}</strong>
                                                @if($category->pivot->is_active)
                                                    <span class="badge bg-success-subtle text-success ms-2">Activa</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary ms-2">Inactiva</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('manager.settings.suppliers.categories.update', [$supplier->uid, $category->id]) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="input-group input-group-sm" style="max-width: 150px;">
                                                        <input type="number" class="form-control" name="priority"
                                                               value="{{ $category->pivot->priority }}" min="0" max="100">
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                @php
                                                    $promptsCount = $supplier->prompts()->where('category_id', $category->id)->count();
                                                @endphp
                                                @if($promptsCount > 0)
                                                    <span class="badge bg-info-subtle text-info">{{ $promptsCount }} prompts</span>
                                                @else
                                                    <small class="text-muted">Sin prompts</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $category->pivot->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('manager.settings.suppliers.categories.toggle', [$supplier->uid, $category->id]) }}"
                                                      method="POST" class="toggle-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="form-check form-switch d-inline-block">
                                                        <input type="checkbox" class="form-check-input toggle-checkbox" role="switch"
                                                               {{ $category->pivot->is_active ? 'checked' : '' }}
                                                               onchange="this.form.submit()">
                                                    </div>
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fa-duotone fa-solid fa-ellipsis"></i>
                                                    </a>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('manager.settings.suppliers.prompts.create') }}?supplier_id={{ $supplier->id }}&category_id={{ $category->id }}&scope=supplier_category">
                                                                <i class="fas fa-magic me-2"></i>Crear prompt
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button type="button"
                                                                    class="dropdown-item text-danger delete-btn"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#delete-modal"
                                                                    data-url="{{ route('manager.settings.suppliers.categories.destroy', [$supplier->uid, $category->id]) }}"
                                                                    data-title="Eliminar categoría: {{ $category->title }}">
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
                                    <i class="fas fa-folder-open fs-7"></i>
                                </div>
                                <h6 class="mb-1">No hay categorías asignadas</h6>
                                <p class="text-muted mb-3">Asigna categorías a este proveedor para organizar tus productos</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('managers.components.delete')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle form handling
    $('.toggle-form').on('submit', function() {
        $(this).find('.toggle-checkbox').prop('disabled', true);
    });

    // Delete modal functionality
    $('.delete-btn').on('click', function() {
        const deleteUrl = $(this).data('url');
        const deleteTitle = $(this).data('title');

        $('#delete-modal .modal-title').text(deleteTitle);
        $('#delete-form').attr('action', deleteUrl);
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
