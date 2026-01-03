@extends('layouts.theme')


@section('title', 'Permisos del grupo: ' . $group->name)

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('settings.documents.configurations') }}">Documentos</a></li>
                <li class="breadcrumb-item"><a href="{{ route('settings.documents.groups.index') }}">Grupos de
                        validación</a></li>
                <li class="breadcrumb-item"><a href="{{ route('settings.documents.groups.edit', $group) }}">{{ $group->name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Permisos</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Permisos del grupo</h2>
                <p class="text-muted mb-0">
                    Gestionar permisos para: <strong>{{ $group->name }}</strong>
                    <span class="badge bg-{{ $group->is_active ? 'success' : 'secondary' }} ms-2">
                        {{ $group->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </p>
            </div>
            <div>
                <a href="{{ route('settings.documents.groups.edit', $group) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver al grupo
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('settings.documents.groups.permissions.update', $group) }}" method="POST"
            id="permissionsForm">
            @csrf

            <!-- Statistics Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="p-3">
                                <h4 class="mb-1" id="totalPermissions">{{ $permissionsByCategory->flatten()->count() }}</h4>
                                <p class="text-muted mb-0">Permisos disponibles</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <h4 class="mb-1 text-success" id="selectedPermissions">{{ count($currentPermissions) }}</h4>
                                <p class="text-muted mb-0">Permisos asignados</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <h4 class="mb-1" id="categoryCount">{{ $permissionsByCategory->count() }}</h4>
                                <p class="text-muted mb-0">Categorías</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <h4 class="mb-1" id="userCount">{{ $group->users()->count() }}</h4>
                                <p class="text-muted mb-0">Usuarios en el grupo</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Acciones rápidas</h6>
                    <div class="row g-2">
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                                <i class="fas fa-check-double me-1"></i>Seleccionar todos
                            </button>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                                <i class="fas fa-times me-1"></i>Deseleccionar todos
                            </button>
                        </div>
                        @if ($permissionsByCategory->count() > 1)
                            <div class="col-auto">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="fas fa-layer-group me-1"></i>Por categoría
                                    </button>
                                    <ul class="dropdown-menu">
                                        @foreach ($permissionsByCategory as $category => $permissions)
                                            <li>
                                                <a class="dropdown-item select-category" href="#"
                                                    data-category="{{ $category }}">
                                                    <i class="fas fa-check me-2"></i>Seleccionar {{ ucfirst($category) }}
                                                </a>
                                            </li>
                                        @endforeach
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        @foreach ($permissionsByCategory as $category => $permissions)
                                            <li>
                                                <a class="dropdown-item deselect-category" href="#"
                                                    data-category="{{ $category }}">
                                                    <i class="fas fa-times me-2"></i>Deseleccionar
                                                    {{ ucfirst($category) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Permissions by Category -->
            @foreach ($permissionsByCategory as $category => $permissions)
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-capitalize">
                                <i class="fas fa-{{ $categoryIcons[$category] ?? $categoryIcons['default'] }} me-2"></i>
                                {{ $categoryLabels[$category] ?? ucfirst($category) }}
                            </h6>
                            <span class="badge bg-primary category-count" data-category="{{ $category }}">
                                <span class="selected-count">{{ $permissions->whereIn('id', $currentPermissions)->count() }}</span>
                                /
                                <span class="total-count">{{ $permissions->count() }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($permissions as $permission)
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check p-3 border rounded permission-checkbox"
                                        data-category="{{ $category }}">
                                        <input class="form-check-input permission-input" type="checkbox" name="permissions[]"
                                            value="{{ $permission->id }}" id="permission_{{ $permission->id }}"
                                            {{ in_array($permission->id, $currentPermissions) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="permission_{{ $permission->id }}">
                                            <strong class="d-block">{{ $permission->label }}</strong>
                                            <small class="text-muted d-block mt-1">{{ $permission->description }}</small>
                                            <small class="badge bg-light text-dark mt-2">{{ $permission->name }}</small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Submit Buttons -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Guardar cambios
                            </button>
                            <a href="{{ route('settings.documents.groups.edit', $group) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                        <div class="text-muted">
                            <small><i class="fas fa-info-circle me-1"></i>Los cambios se aplicarán inmediatamente a todos
                                los usuarios del grupo</small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('permissionsForm');
            const permissionInputs = document.querySelectorAll('.permission-input');
            const selectedCountEl = document.getElementById('selectedPermissions');

            // Update selected count
            function updateSelectedCount() {
                const selectedCount = document.querySelectorAll('.permission-input:checked').length;
                selectedCountEl.textContent = selectedCount;

                // Update category counts
                document.querySelectorAll('.category-count').forEach(badge => {
                    const category = badge.dataset.category;
                    const categoryInputs = document.querySelectorAll(
                        `.permission-checkbox[data-category="${category}"] .permission-input`);
                    const selectedInCategory = Array.from(categoryInputs).filter(input => input.checked)
                        .length;
                    badge.querySelector('.selected-count').textContent = selectedInCategory;
                });
            }

            // Select all permissions
            document.getElementById('selectAll').addEventListener('click', function() {
                permissionInputs.forEach(input => input.checked = true);
                updateSelectedCount();
            });

            // Deselect all permissions
            document.getElementById('deselectAll').addEventListener('click', function() {
                permissionInputs.forEach(input => input.checked = false);
                updateSelectedCount();
            });

            // Select by category
            document.querySelectorAll('.select-category').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const category = this.dataset.category;
                    document.querySelectorAll(
                        `.permission-checkbox[data-category="${category}"] .permission-input`
                    ).forEach(input => input.checked = true);
                    updateSelectedCount();
                });
            });

            // Deselect by category
            document.querySelectorAll('.deselect-category').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const category = this.dataset.category;
                    document.querySelectorAll(
                        `.permission-checkbox[data-category="${category}"] .permission-input`
                    ).forEach(input => input.checked = false);
                    updateSelectedCount();
                });
            });

            // Update counts on checkbox change
            permissionInputs.forEach(input => {
                input.addEventListener('change', updateSelectedCount);
            });

            // Confirm before submit if many permissions are being removed
            form.addEventListener('submit', function(e) {
                const initialSelected = {{ count($currentPermissions) }};
                const currentSelected = document.querySelectorAll('.permission-input:checked').length;
                const removedCount = initialSelected - currentSelected;

                if (removedCount > 5) {
                    if (!confirm(
                            `Estás a punto de remover ${removedCount} permisos. Esto afectará a ${document.getElementById('userCount').textContent} usuarios. ¿Deseas continuar?`
                        )) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
@endpush
