@extends('layouts.theme')

@section('content')
    <div class="row">
        <div class="col-12">
            @include('theme.components.card', ['title' => 'Editar evento'])

            <div class="card">
                {{-- Header --}}
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-bold">{{ $event->title }}</h5>
                            <p class="text-muted mb-0 small">Modifica los detalles del evento para sincronizar con PrestaShop</p>
                        </div>
                        <div>
                            <a href="{{ route('manager.events.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <form id="formEvent" method="POST" class="needs-validation" novalidate>
                    @csrf

                    <div class="card-body">
                        {{-- Información básica --}}
                        <div class="border-bottom pb-4 mb-4">
                            <h6 class="mb-3 fw-bold">Información básica</h6>
                            <p class="text-muted small mb-3">Datos esenciales del evento</p>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="title" class="form-label">Título del evento <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           id="title" name="title" value="{{ $event->title }}" placeholder="Ej: Black Friday 2024" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="start_at" class="form-label">Fecha de inicio <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('start_at') is-invalid @enderror"
                                           id="start_at" name="start_at" value="{{ $event->start_at?->format('Y-m-d\TH:i') }}" required>
                                    @error('start_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="end_at" class="form-label">Fecha de finalización <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('end_at') is-invalid @enderror"
                                           id="end_at" name="end_at" value="{{ $event->end_at?->format('Y-m-d\TH:i') }}" required>
                                    @error('end_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Configuración visual --}}
                        <div class="border-bottom pb-4 mb-4">
                            <h6 class="mb-3 fw-bold">Configuración visual</h6>
                            <p class="text-muted small mb-3">Apariencia del evento en la tienda</p>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="color_flag" class="form-label">Color del evento</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color"
                                               id="color_flag" name="color_flag" value="{{ $event->color_flag ?? '#90bb13' }}" style="max-width: 60px;">
                                        <span class="input-group-text" id="color_preview" style="background-color: {{ $event->color_flag ?? '#90bb13' }}; color: white;">
                                            Azúcar
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="available" class="form-label">Visible en tienda</label>
                                    <select class="form-select" id="available" name="available">
                                        <option value="1" @selected($event->available == 1)>Sí, publicado</option>
                                        <option value="0" @selected($event->available == 0)>No, oculto</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-12 col-md-6">
                                    <label for="featured" class="form-label">Destacado</label>
                                    <select class="form-select" id="featured" name="featured">
                                        <option value="0" @selected($event->featured == 0)>No</option>
                                        <option value="1" @selected($event->featured == 1)>Sí, mostrar destacado</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="amazing" class="form-label">Oferta especial</label>
                                    <select class="form-select" id="amazing" name="amazing">
                                        <option value="0" @selected($event->amazing == 0)>No</option>
                                        <option value="1" @selected($event->amazing == 1)>Sí, mostrar como especial</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Detalles comerciales --}}
                        <div class="border-bottom pb-4 mb-4">
                            <h6 class="mb-3 fw-bold">Detalles comerciales</h6>
                            <p class="text-muted small mb-3">Información de precios e impuestos</p>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="iva" class="form-label">IVA / Impuesto (%)</label>
                                    <input type="number" class="form-control"
                                           id="iva" name="iva" step="0.01" min="0" max="100" value="{{ $event->iva }}" placeholder="21.00">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="filter_flag" class="form-label">Etiqueta de filtro</label>
                                    <input type="text" class="form-control"
                                           id="filter_flag" name="filter_flag" value="{{ $event->filter_flag }}" placeholder="Ej: descuentos, promociones">
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-12 col-md-6">
                                    <label for="management_flag" class="form-label">Etiqueta de gestión</label>
                                    <input type="text" class="form-control"
                                           id="management_flag" name="management_flag" value="{{ $event->management_flag }}" placeholder="Ej: interno, control">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="priority_flag" class="form-label">Prioridad</label>
                                    <input type="number" class="form-control"
                                           id="priority_flag" name="priority_flag" min="0" max="100" value="{{ $event->priority_flag }}" placeholder="50">
                                </div>
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div class="pb-4">
                            <h6 class="mb-3 fw-bold">Observaciones</h6>
                            <p class="text-muted small mb-3">Notas internas sobre el evento</p>

                            <div class="row">
                                <div class="col-12">
                                    <label for="observations" class="form-label">Notas y observaciones</label>
                                    <textarea class="form-control" id="observations" name="observations"
                                              rows="4" placeholder="Escribe aquí cualquier nota o comentario sobre este evento...">{{ $event->observations }}</textarea>
                                    <small class="text-muted">Estas notas son internas y no se mostrarán en la tienda</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer con botones --}}
                    <div class="card-footer bg-light border-top">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('manager.events.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Actualizar evento
                            </button>
                        </div>
                    </div>

                    {{-- Hidden field for UID --}}
                    <input type="hidden" name="uid" value="{{ $event->uid }}">
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Color picker update
    $('#color_flag').on('change', function() {
        const color = $(this).val();
        $('#color_preview').css('background-color', color);
    });

    // Form submission
    $('#formEvent').on('submit', function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const submitBtn = $(this).find('button[type="submit"]');
        const originalHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Actualizando...');

        $.ajax({
            url: '{{ route("manager.events.update") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message, 'Éxito', { positionClass: 'toast-bottom-right' });
                    setTimeout(() => {
                        window.location.href = '{{ route("manager.events.index") }}';
                    }, 1500);
                } else {
                    toastr.error(response.message, 'Error', { positionClass: 'toast-bottom-right' });
                }
            },
            error: function(xhr) {
                let message = 'Error al actualizar el evento';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message, 'Error', { positionClass: 'toast-bottom-right' });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Bootstrap form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<style>
    .form-control-color {
        height: auto;
        padding: 0.375rem 0.75rem;
    }
</style>
@endpush
