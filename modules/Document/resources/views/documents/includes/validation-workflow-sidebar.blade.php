{{-- Validation Workflow Sidebar --}}
@php
    $hasWorkflow = $document->total_stages > 0;
    $isInValidation = in_array($document->validation_status, ['pending', 'in_validation']);
    $canApproveStage = $hasWorkflow && $isInValidation && $document->current_stage <= $document->total_stages;

    // Get validation history
    $validationHistory = $document->validationHistory()->with('validator')->orderBy('validated_at', 'desc')->get();

    // Progress percentage
    $progressPercentage = $hasWorkflow ? (($document->current_stage - 1) / $document->total_stages) * 100 : 0;
    if ($document->validation_status === 'approved') {
        $progressPercentage = 100;
    }

    // Status badge config - all using primary color
    $statusLabels = [
        'pending' => 'Pendiente',
        'in_validation' => 'En validación',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
    ];
    $currentStatusLabel = $statusLabels[$document->validation_status] ?? 'Pendiente';
@endphp

@if($hasWorkflow)
<div class="card mb-3">
    <div class="card-header p-3 bg-white border-bottom">
        <h5 class="mb-1 fw-bold">Validación Etapa</h5>
        <p class="small mb-0 text-muted">Progreso de validación del documento</p>
    </div>
    <div class="card-body">
        {{-- Progress Section --}}
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-semibold text-dark">
                    Etapa {{ $document->current_stage }} de {{ $document->total_stages }}
                </span>
                <span class="badge bg-primary-subtle text-primary">
                    {{ $currentStatusLabel }}
                </span>
            </div>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-primary" role="progressbar"
                     style="width: {{ $progressPercentage }}%"
                     aria-valuenow="{{ $progressPercentage }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
        </div>

        {{-- Current Stage Info --}}
        @if($isInValidation)
            <div class="alert bg-light-subtle py-3 px-3 mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas fa-user-check text-primary me-2 mt-1" style="font-size: 0.9rem;"></i>
                    <div>
                        <small class="fw-semibold d-block text-dark">Grupo actual</small>
                        @if($document->current_validator_group)
                            <small class="text-muted">{{ ucfirst($document->current_validator_group) }}</small>
                        @else
                            <small class="text-muted">Pendiente de asignación</small>
                        @endif

                        @if($document->assigned_user_id && $document->assignedUser)
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                Asignado a: {{ $document->assignedUser->full_name }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Action Buttons --}}
        @if($canApproveStage)
            <div class="d-grid gap-2 mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#approveStageModal">
                    Aprobar
                </button>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#rejectStageModal">
                    Rechazar
                </button>
            </div>
        @elseif($document->validation_status === 'approved')
            <div class="alert bg-primary-subtle py-3 px-3 mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas fa-check-circle text-primary me-2 mt-1" style="font-size: 0.9rem;"></i>
                    <div>
                        <small class="fw-semibold d-block text-primary">Documento aprobado</small>
                        <small class="text-muted">Todas las etapas han sido completadas</small>
                    </div>
                </div>
            </div>
        @elseif($document->validation_status === 'rejected')
            <div class="alert bg-primary-subtle py-3 px-3 mb-3" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-circle text-primary me-2 mt-1" style="font-size: 0.9rem;"></i>
                    <div>
                        <small class="fw-semibold d-block text-primary">Documento rechazado</small>
                        <small class="text-muted">Requiere corrección por parte del cliente</small>
                    </div>
                </div>
            </div>
        @endif

        {{-- Validation History --}}
        @if($validationHistory->count() > 0)
            <div class="border-top my-3"></div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="fw-semibold text-dark">Historial de validación</small>
                <span class="badge bg-primary-subtle text-primary">{{ $validationHistory->count() }}</span>
            </div>

            <div class="validation-history-scroll">
                @foreach($validationHistory as $history)
                    <div class="validation-item border-bottom py-2">
                        <div class="d-flex align-items-start gap-2">
                            {{-- Icon - all using primary color --}}
                            @if($history->action === 'approved')
                                <i class="fas fa-check-circle text-primary mt-1" style="font-size: 0.85rem;"></i>
                            @elseif($history->action === 'rejected')
                                <i class="fas fa-times-circle text-primary mt-1" style="font-size: 0.85rem;"></i>
                            @else
                                <i class="fas fa-undo text-primary mt-1" style="font-size: 0.85rem;"></i>
                            @endif

                            {{-- Content --}}
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="fw-semibold d-block text-dark">
                                            Etapa {{ $history->stage_number }} - {{ ucfirst($history->validator_group) }}
                                        </small>
                                        <small class="text-muted">
                                            {{ $history->action === 'approved' ? 'Aprobado' : ($history->action === 'rejected' ? 'Rechazado' : 'Devuelto') }}
                                            por {{ $history->validator->full_name ?? 'Sistema' }}
                                        </small>
                                    </div>
                                    <small class="text-muted flex-shrink-0" style="font-size: 0.7rem;">
                                        {{ $history->validated_at->format('d/m H:i') }}
                                    </small>
                                </div>
                                @if($history->comments)
                                    <small class="text-muted d-block mt-1 fst-italic">
                                        "{{ Str::limit($history->comments, 80) }}"
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="border-top my-3"></div>
            <div class="alert bg-light-subtle py-3 px-3 mb-0" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas fa-circle-info text-primary me-2 mt-1" style="font-size: 0.9rem;"></i>
                    <div>
                        <small class="fw-semibold d-block">Sin historial</small>
                        <small class="text-muted">No hay acciones de validación registradas aún.</small>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endif

@push('styles')
<style>
    .validation-history-scroll {
        overflow-y: auto;
        max-height: 200px;
    }

    @media (min-width: 576px) { .validation-history-scroll { max-height: 220px; } }
    @media (min-width: 768px) { .validation-history-scroll { max-height: 250px; } }
    @media (min-width: 992px) { .validation-history-scroll { max-height: 280px; } }

    .validation-history-scroll::-webkit-scrollbar {
        width: 5px;
    }

    .validation-history-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .validation-history-scroll::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 2px;
    }

    .validation-history-scroll::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }

    .validation-item {
        transition: all 0.2s ease;
        border-color: #e9ecef !important;
    }

    .validation-item:hover {
        background-color: #f8f9fa;
    }

    .validation-item:last-child {
        border-bottom: none !important;
    }

    .min-width-0 {
        min-width: 0;
    }
</style>
@endpush

{{-- MODALES DE WORKFLOW --}}
@include('documents::modals.approve-stage')
@include('documents::modals.reject-stage')
