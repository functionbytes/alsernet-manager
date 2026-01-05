@extends('layouts.theme')

@section('content')

    @include('theme.components.card', ['title' => 'Gestionar Módulos del Rol'])

    {{-- Alertas --}}
    @include('theme.components.alerts')

    <div class="card">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Módulos disponibles para: <strong>{{ $role->name }}</strong></h5>
                    <p class="text-muted mb-0 small">Selecciona los módulos que este rol puede visualizar en el panel</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('settings.roles.edit', $role->id) }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Atrás
                    </a>
                </div>
            </div>
        </div>

        <form id="formModules" enctype="multipart/form-data" onSubmit="return false">
            @csrf
            <input type="hidden" name="role_id" value="{{ $role->id }}">

            <div class="card-body">
                {{-- Info --}}
                <div class="alert alert-info border-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Nota:</strong> Los usuarios con este rol solo verán los módulos que estén habilitados aquí en su panel de navegación.
                    Los super-admin siempre ven todos los módulos disponibles.
                </div>

                {{-- Módulos Grid --}}
                <div class="row">
                    @forelse($modules as $moduleId => $moduleName)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body p-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input module-checkbox"
                                               type="checkbox"
                                               name="modules[]"
                                               id="module_{{ $moduleId }}"
                                               value="{{ $moduleId }}"
                                               {{ in_array($moduleId, $roleModules) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="module_{{ $moduleId }}">
                                            <strong>{{ $moduleName }}</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No hay módulos disponibles
                            </div>
                        </div>
                    @endforelse
                </div>

                <hr class="my-4">

                {{-- Quick Actions --}}
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Acciones rápidas</h6>
                    <div class="btn-group mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary" id="selectAll">
                            <i class="fas fa-check-double me-2"></i>Seleccionar todo
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="deselectAll">
                            <i class="fas fa-times me-2"></i>Desseleccionar todo
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('settings.roles.edit', $role->id) }}" class="btn btn-light w-100 mb-2">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select All
    $('#selectAll').on('click', function() {
        $('.module-checkbox').prop('checked', true);
    });

    // Deselect All
    $('#deselectAll').on('click', function() {
        $('.module-checkbox').prop('checked', false);
    });

    // Form submission
    $('#formModules').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');

        $.ajax({
            url: "{{ route('settings.roles.update.modules', $role->id) }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                toastr.success(response.message || 'Módulos actualizados correctamente');
                setTimeout(function() {
                    window.location.href = "{{ route('settings.roles.edit', $role->id) }}";
                }, 1500);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Guardar Cambios');

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error('Error al actualizar módulos. Intente nuevamente.');
                }

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        toastr.error(value[0]);
                    });
                }
            }
        });
    });
});
</script>
@endpush
