@extends('layouts.managers.app')

@section('content')
<div class="container-xxl">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('manager.settings.stage-email-actions.index') }}" class="btn btn-sm btn-link p-0 me-2">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h5 class="d-inline mb-0">
                                <i class="fas fa-cog me-2"></i>
                                Configurar acciones de email: <strong>{{ $stageName }}</strong>
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('manager.settings.stage-email-actions.update', $stage) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Selecciona cuáles acciones de email estarán disponibles en la etapa de <strong>{{ $stageName }}</strong>.
                        </div>

                        <div class="row">
                            @foreach($configurations as $config)
                                @php
                                    $action = $config->email_action;
                                    $label = $availableActions[$action] ?? $action;
                                    $isEnabled = old("email_actions.{$action}", $config->is_enabled ?? false);
                                @endphp

                                <div class="col-md-6 mb-4">
                                    <div class="card border @if($isEnabled) border-success @else border-light @endif">
                                        <div class="card-body">
                                            <div class="form-check form-switch">
                                                <input
                                                    class="form-check-input action-toggle"
                                                    type="checkbox"
                                                    id="action_{{ $action }}"
                                                    name="email_actions[{{ $action }}]"
                                                    value="1"
                                                    @if($isEnabled) checked @endif
                                                    data-action="{{ $action }}"
                                                >
                                                <label class="form-check-label fw-bold" for="action_{{ $action }}">
                                                    {{ $label }}
                                                </label>
                                            </div>

                                            <input type="hidden" name="sort_order[{{ $action }}]" class="sort-order" value="{{ old("sort_order.{$action}", $config->sort_order ?? 0) }}">

                                            <div class="mt-3 ps-3 d-flex gap-2 flex-wrap">
                                                <button type="button" class="btn btn-sm btn-outline-secondary sort-up" data-action="{{ $action }}">
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary sort-down" data-action="{{ $action }}">
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                                <span class="badge bg-secondary sort-order-display" data-action="{{ $action }}">
                                                    {{ old("sort_order.{$action}", $config->sort_order ?? 0) }}
                                                </span>
                                            </div>

                                            @switch($action)
                                                @case('solicitud_documentos')
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-envelope me-1"></i>
                                                        Envía una solicitud inicial de documentos al cliente.
                                                    </small>
                                                    @break

                                                @case('confirmacion_archivos')
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-file me-1"></i>
                                                        Confirma al cliente que sus archivos fueron recibidos.
                                                    </small>
                                                    @break

                                                @case('aprobacion')
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Notifica al cliente que sus documentos fueron aprobados.
                                                    </small>
                                                    @break

                                                @case('rechazo')
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-times-circle me-1"></i>
                                                        Notifica al cliente que sus documentos fueron rechazados.
                                                    </small>
                                                    @break

                                                @case('correo_personalizado')
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-pen me-1"></i>
                                                        Permite enviar un correo personalizado al cliente.
                                                    </small>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-top pt-3 mt-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('manager.settings.stage-email-actions.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar configuración
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle sort order updates
    document.querySelectorAll('.sort-up').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const input = document.querySelector(`.sort-order[data-action="${action}"]`);
            if (input) {
                input.value = Math.max(0, parseInt(input.value) - 1);
                updateSortOrderDisplay(action);
            }
        });
    });

    document.querySelectorAll('.sort-down').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const input = document.querySelector(`input[name="sort_order[${action}]"]`);
            if (input) {
                input.value = parseInt(input.value) + 1;
                updateSortOrderDisplay(action);
            }
        });
    });

    function updateSortOrderDisplay(action) {
        const input = document.querySelector(`input[name="sort_order[${action}]"]`);
        const display = document.querySelector(`.sort-order-display[data-action="${action}"]`);
        if (input && display) {
            display.textContent = input.value;
        }
    }

    // Update card border color on toggle
    document.querySelectorAll('.action-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const card = this.closest('.card');
            if (this.checked) {
                card.classList.remove('border-light');
                card.classList.add('border-success');
            } else {
                card.classList.remove('border-success');
                card.classList.add('border-light');
            }
        });
    });
});
</script>
@endsection
