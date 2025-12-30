@extends('layouts.managers')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-0 fw-bold">Biblioteca de plantillas de prompts</h5>
                            <p class="card-subtitle mb-0 mt-2">Plantillas reutilizables para crear prompts rápidamente</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('manager.settings.suppliers.templates.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Crear Plantilla
                            </a>
                            <a href="{{ route('manager.settings.suppliers.prompts.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-1"></i> Volver a Prompts
                            </a>
                        </div>
                    </div>

                    @include('managers.components.alerts')

                    <!-- Stats Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light-primary h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-primary mb-2">Total plantillas</h6>
                                    <h4 class="mb-1 fw-bold">{{ $stats['total_templates'] }}</h4>
                                    <small class="text-muted">Disponibles para clonar</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-success h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-success mb-2">Prompts creados</h6>
                                    <h4 class="mb-1 fw-bold">{{ $stats['total_cloned'] }}</h4>
                                    <small class="text-muted">Desde plantillas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-info h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-info mb-2">Categorías</h6>
                                    <h4 class="mb-1 fw-bold">{{ $stats['categories_count'] }}</h4>
                                    <small class="text-muted">De plantillas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card border mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('manager.settings.suppliers.templates.index') }}" class="row align-items-end g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Categoría de plantilla</label>
                                    <select class="form-select" name="category">
                                        <option value="">Todas las categorías</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                                {{ ucfirst($cat) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Tipo de contenido</label>
                                    <select class="form-select" name="content_type">
                                        <option value="">Todos los tipos</option>
                                        @foreach($contentTypes as $type)
                                            <option value="{{ $type }}" {{ request('content_type') == $type ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-1"></i> Filtrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Templates Grid -->
                    @if($templates->count() > 0)
                        <div class="row g-4">
                            @foreach($templates as $template)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border h-100">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold">{{ $template->label }}</h6>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        @if($template->template_category)
                                                            <span class="badge bg-info-subtle text-info">
                                                                <i class="fas fa-folder me-1"></i>{{ $template->template_category }}
                                                            </span>
                                                        @endif
                                                        <span class="badge bg-primary-subtle text-primary">
                                                            <i class="fas fa-file-alt me-1"></i>{{ str_replace('_', ' ', $template->content_type) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Vista previa del prompt:</small>
                                                <div class="bg-light p-2 rounded" style="max-height: 120px; overflow-y: auto; font-size: 0.85rem;">
                                                    {{ Str::limit($template->prompt_template, 200) }}
                                                </div>
                                            </div>

                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Idioma</small>
                                                    <span class="badge bg-secondary-subtle text-secondary">{{ strtoupper($template->output_language) }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Tono</small>
                                                    <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($template->tone) }}</span>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted d-block">SEO Focus</small>
                                                <span class="badge {{ $template->seo_focus ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                    {{ $template->seo_focus ? 'Sí' : 'No' }}
                                                </span>
                                            </div>

                                            @php
                                                $clonedCount = $template->clonedPrompts()->count();
                                            @endphp
                                            @if($clonedCount > 0)
                                                <div class="alert alert-info py-2 mb-3">
                                                    <i class="fas fa-clone me-1"></i>
                                                    <small>{{ $clonedCount }} prompt(s) creado(s) desde esta plantilla</small>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-footer bg-white">
                                            <div class="d-flex gap-2 mb-2">
                                                <a href="{{ route('manager.settings.suppliers.templates.edit', $template->uid) }}"
                                                   class="btn btn-light flex-fill">
                                                    <i class="fas fa-edit me-1"></i> Editar
                                                </a>
                                                <button type="button"
                                                        class="btn btn-danger delete-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#delete-modal"
                                                        data-url="{{ route('manager.settings.suppliers.templates.destroy', $template->uid) }}"
                                                        data-title="Eliminar plantilla: {{ $template->label }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <button type="button"
                                                    class="btn btn-success w-100 clone-template-btn"
                                                    data-template-uid="{{ $template->uid }}"
                                                    data-template-label="{{ $template->label }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cloneModal">
                                                <i class="fas fa-copy me-1"></i> Clonar plantilla
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <div class="round-48 rounded-circle bg-light-subtle text-muted mb-3 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-file-alt fs-7"></i>
                                </div>
                                <h6 class="mb-1">No hay plantillas disponibles</h6>
                                <p class="text-muted mb-3">No se encontraron plantillas con los filtros seleccionados</p>
                                <a href="{{ route('manager.settings.suppliers.templates.index') }}" class="btn btn-primary">
                                    <i class="fas fa-refresh me-1"></i> Limpiar filtros
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Clone Modal -->
    <div class="modal fade" id="cloneModal" tabindex="-1" aria-labelledby="cloneModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="cloneForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="cloneModalLabel">Clonar plantilla</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Plantilla seleccionada:</strong> <span id="selectedTemplateName"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre del nuevo prompt <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="label" required placeholder="Ej: Nike Golf - Descripción Detallada">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Scope <span class="text-danger">*</span></label>
                            <select class="form-select" name="scope" id="scopeSelect" required>
                                <option value="">Seleccionar scope...</option>
                                <option value="global">Global (todos los productos)</option>
                                <option value="supplier">Proveedor específico</option>
                                <option value="category">Categoría específica</option>
                                <option value="supplier_category">Proveedor + Categoría (más específico)</option>
                            </select>
                            <small class="text-muted">El scope determina cuándo se usará este prompt</small>
                        </div>

                        <div id="supplierContainer" class="mb-3" style="display: none;">
                            <label class="form-label fw-semibold">Proveedor <span class="text-danger">*</span></label>
                            <select class="form-select" name="supplier_id" id="supplierSelect">
                                <option value="">Seleccionar proveedor...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="categoryContainer" class="mb-3" style="display: none;">
                            <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="categorySelect">
                                <option value="">Seleccionar categoría...</option>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Prioridad</label>
                            <input type="number" class="form-control" name="priority" value="0" min="0" max="100">
                            <small class="text-muted">Mayor prioridad = se selecciona primero (0-100)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-copy me-1"></i> Crear prompt desde plantilla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('managers.components.delete')
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle clone modal
    $('.clone-template-btn').on('click', function() {
        const templateUid = $(this).data('template-uid');
        const templateLabel = $(this).data('template-label');

        $('#selectedTemplateName').text(templateLabel);
        $('#cloneForm').attr('action', '/manager/settings/suppliers/templates/' + templateUid + '/clone');
    });

    // Handle scope change to show/hide supplier and category selects
    $('#scopeSelect').on('change', function() {
        const scope = $(this).val();

        // Hide all containers first
        $('#supplierContainer').hide();
        $('#categoryContainer').hide();

        // Remove required attribute
        $('#supplierSelect').prop('required', false);
        $('#categorySelect').prop('required', false);

        // Show relevant containers based on scope
        if (scope === 'supplier' || scope === 'supplier_category') {
            $('#supplierContainer').show();
            $('#supplierSelect').prop('required', true);
        }

        if (scope === 'category' || scope === 'supplier_category') {
            $('#categoryContainer').show();
            $('#categorySelect').prop('required', true);
        }
    });

    // Handle delete modal
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
