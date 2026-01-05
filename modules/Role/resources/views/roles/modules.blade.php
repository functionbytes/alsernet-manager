@extends('layouts.theme')

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb Card --}}
    @include('theme.components.card', [
        'title' => 'Gestionar módulos del rol',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => url('/home')],
            ['label' => 'Roles', 'url' => route('settings.roles.index')],
            ['label' => $role->name, 'url' => route('settings.roles.edit', $role->id)],
            ['label' => 'Módulos', 'active' => true]
        ]
    ])

    <div class="widget-content searchable-container list">

        {{-- Main Card --}}
        <div class="card">
            {{-- Header Section --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Módulos visibles para: {{ $role->name }}</h5>
                        <p class="small mb-0 text-muted">Selecciona los módulos que este rol puede visualizar en el panel de navegación</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('settings.roles.edit', $role->id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    @php
                        $totalModules = count($modules);
                        $enabledModules = count($roleModules);
                        $disabledModules = $totalModules - $enabledModules;
                        $coveragePercentage = $totalModules > 0 ? round(($enabledModules / $totalModules) * 100) : 0;
                    @endphp
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-primary mb-2">Total</h6>
                                        <h4 class="mb-1 fw-bold">{{ $totalModules }}</h4>
                                        <small class="text-muted">Módulos disponibles</small>
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
                                        <h6 class="card-title text-success mb-2">Habilitados</h6>
                                        <h4 class="mb-1 fw-bold module-total-count">{{ $enabledModules }}</h4>
                                        <small class="text-muted">Módulos activos</small>
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
                                        <h6 class="card-title text-warning mb-2">Deshabilitados</h6>
                                        <h4 class="mb-1 fw-bold module-disabled-count">{{ $disabledModules }}</h4>
                                        <small class="text-muted">Módulos inactivos</small>
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
                                        <h6 class="card-title text-info mb-2">Cobertura</h6>
                                        <h4 class="mb-1 fw-bold module-percentage">{{ $coveragePercentage }}%</h4>
                                        <small class="text-muted">Módulos asignados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Section --}}
            <div class="card-body border-bottom">
                <div class="alert alert-info border-0 mb-0" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fa fa-circle-info fs-5 me-3 mt-1"></i>
                        <div>
                            <h6 class="fw-bold mb-2">Control de visibilidad de módulos</h6>
                            <p class="mb-0">
                                Los usuarios con este rol solo verán los módulos habilitados aquí en su panel de navegación.
                                Los super-admin siempre tienen acceso a todos los módulos del sistema.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alerts --}}
            @if ($errors->any())
                <div class="card-body border-bottom">
                    <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fa fa-exclamation-circle fs-4 me-2 mt-1"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-2">Errores de validación</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="card-body border-bottom">
                    <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-check fs-4 me-2"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @include('theme.components.alerts')

            <form id="formModules" enctype="multipart/form-data" onSubmit="return false">
                @csrf
                <input type="hidden" name="role_id" value="{{ $role->id }}">

                {{-- Quick Actions Bar --}}
                <div class="card-body border-bottom bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-semibold">Acciones rápidas</h6>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="selectAll">
                                <i class="fas fa-check-double me-1"></i>Seleccionar todo
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAll">
                                <i class="fas fa-times me-1"></i>Deseleccionar todo
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Modules Grid --}}
                @if(count($modules) > 0)
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($modules as $moduleId => $moduleName)
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <div class="card h-100 border module-card {{ in_array($moduleId, $roleModules) ? 'border-primary' : '' }}">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                             style="width: 35px; height: 35px; background-color: {{ in_array($moduleId, $roleModules) ? 'rgba(144, 187, 19, 0.1)' : '#f8f9fa' }};">
                                                            <i class="fas fa-cube {{ in_array($moduleId, $roleModules) ? 'text-primary' : 'text-muted' }}"></i>
                                                        </div>
                                                        <div class="form-check form-switch mb-0">
                                                            <input class="form-check-input module-checkbox"
                                                                   type="checkbox"
                                                                   name="modules[]"
                                                                   id="module_{{ $moduleId }}"
                                                                   value="{{ $moduleId }}"
                                                                   {{ in_array($moduleId, $roleModules) ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                    <label class="form-check-label d-block" for="module_{{ $moduleId }}" style="cursor: pointer;">
                                                        <strong class="d-block mb-1">{{ $moduleName }}</strong>
                                                        @if(in_array($moduleId, $roleModules))
                                                            <span class="badge bg-success-subtle text-success small">Habilitado</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary small">Deshabilitado</span>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <div class="text-center py-5">
                            <i class="fa fa-inbox fa-3x mb-3 text-muted opacity-50"></i>
                            <h5 class="fw-bold mb-2">No hay módulos disponibles</h5>
                            <p class="text-muted mb-4">
                                No se encontraron módulos configurados en el sistema.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Summary Section --}}
                @if(count($modules) > 0)
                    <div class="card-body border-top bg-light">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 45px; height: 45px; background-color: rgba(144, 187, 19, 0.1);">
                                        <i class="fas fa-cubes text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold module-total-count">{{ count($roleModules) }}</h6>
                                        <small class="text-muted">Módulos habilitados</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 45px; height: 45px; background-color: rgba(83, 109, 254, 0.1);">
                                        <i class="fas fa-th-large text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ count($modules) }}</h6>
                                        <small class="text-muted">Total módulos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 45px; height: 45px; background-color: rgba(250, 137, 107, 0.1);">
                                        <i class="fas fa-percent text-danger"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold module-percentage">{{ count($modules) > 0 ? round((count($roleModules) / count($modules)) * 100) : 0 }}%</h6>
                                        <small class="text-muted">Cobertura</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Footer Actions --}}
                <div class="card-footer bg-white border-top">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Guardar cambios
                            </button>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('settings.roles.edit', $role->id) }}" class="btn btn-secondary w-100">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>

        </div>

    </div>

</div>

@endsection

@push('styles')
<style>
    .module-card {
        transition: all 0.2s ease;
        border: 2px solid #dee2e6;
    }

    .module-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .module-card.border-primary {
        background-color: rgba(144, 187, 19, 0.02);
        border-color: #90bb13 !important;
    }

    /* Green hover effect for checked cards */
    .module-card.border-primary:hover {
        background-color: rgba(144, 187, 19, 0.08);
        border-color: #7a9e11 !important;
        box-shadow: 0 4px 12px rgba(144, 187, 19, 0.3);
    }

    /* Normal hover for unchecked cards */
    .module-card:not(.border-primary):hover {
        border-color: #adb5bd;
    }

    .form-check-input:checked {
        background-color: #90bb13;
        border-color: #90bb13;
    }

    .form-check-input:focus {
        border-color: #90bb13;
        box-shadow: 0 0 0 0.25rem rgba(144, 187, 19, 0.25);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Update statistics when checkboxes change
    function updateStats() {
        const totalModules = {{ count($modules) }};
        const enabledCount = $('.module-checkbox:checked').length;
        const disabledCount = totalModules - enabledCount;
        const percentage = totalModules > 0 ? Math.round((enabledCount / totalModules) * 100) : 0;

        $('.module-total-count').text(enabledCount);
        $('.module-disabled-count').text(disabledCount);
        $('.module-percentage').text(percentage + '%');
    }

    // Handle checkbox changes
    $('.module-checkbox').on('change', function() {
        const $card = $(this).closest('.module-card');
        const $badge = $card.find('.badge');
        const $icon = $card.find('.fa-cube');
        const $iconBg = $card.find('.rounded-circle');

        if ($(this).is(':checked')) {
            $card.addClass('border-primary');
            $badge.removeClass('bg-secondary-subtle text-secondary').addClass('bg-success-subtle text-success').text('Habilitado');
            $icon.removeClass('text-muted').addClass('text-primary');
            $iconBg.css('background-color', 'rgba(144, 187, 19, 0.1)');
        } else {
            $card.removeClass('border-primary');
            $badge.removeClass('bg-success-subtle text-success').addClass('bg-secondary-subtle text-secondary').text('Deshabilitado');
            $icon.removeClass('text-primary').addClass('text-muted');
            $iconBg.css('background-color', '#f8f9fa');
        }

        updateStats();
    });

    // Select All
    $('#selectAll').on('click', function() {
        $('.module-checkbox').prop('checked', true).trigger('change');
    });

    // Deselect All
    $('#deselectAll').on('click', function() {
        $('.module-checkbox').prop('checked', false).trigger('change');
    });

    // Form submission
    $('#formModules').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        const originalHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');

        $.ajax({
            url: "{{ route('settings.roles.update.modules', $role->id) }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                toastr.success(response.message || 'Módulos actualizados correctamente', 'Éxito', {
                    positionClass: 'toast-bottom-right'
                });
                setTimeout(function() {
                    window.location.href = "{{ route('settings.roles.edit', $role->id) }}";
                }, 1500);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalHtml);

                let errorMessage = 'Error al actualizar módulos. Intente nuevamente.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                toastr.error(errorMessage, 'Error', {
                    positionClass: 'toast-bottom-right'
                });

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        toastr.error(value[0], 'Error', {
                            positionClass: 'toast-bottom-right'
                        });
                    });
                }
            }
        });
    });
});
</script>
@endpush
