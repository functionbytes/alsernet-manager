{{-- Validation Workflow Sidebar --}}
@php
    $hasWorkflow = $document->total_stages > 0;
    $isInValidation = in_array($document->validation_status, ['pending', 'in_validation']);

    // Verificar si el usuario está en el grupo de validadores actual
    $userIsInValidatorGroup = false;
    if ($isInValidation && $document->current_validator_group) {
        $userIsInValidatorGroup = auth()->user()->documentValidatorGroups()
            ->where('key', $document->current_validator_group)
            ->where('is_active', true)
            ->exists();
    }

    $canApproveStage = $hasWorkflow && $isInValidation && $userIsInValidatorGroup && $document->current_stage <= $document->total_stages;

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
            <div class="alert {{ $userIsInValidatorGroup ? 'bg-success-subtle' : 'bg-warning-subtle' }} py-3 px-3 mb-3" role="alert">
                <div class="d-flex align-items-start">
                    @if($userIsInValidatorGroup)
                        <i class="fas fa-check-circle text-success me-2 mt-1" style="font-size: 0.9rem;"></i>
                    @else
                        <i class="fas fa-info-circle text-warning me-2 mt-1" style="font-size: 0.9rem;"></i>
                    @endif
                    <div>
                        <small class="fw-semibold d-block {{ $userIsInValidatorGroup ? 'text-success' : 'text-warning' }}">
                            Grupo validador {{ $userIsInValidatorGroup ? '(Tu grupo)' : '(No asignado)' }}
                        </small>
                        @if($document->current_validator_group)
                            <small class="text-muted">{{ ucfirst($document->current_validator_group) }}</small>
                        @else
                            <small class="text-muted">Pendiente de asignación</small>
                        @endif

                        @if(!$userIsInValidatorGroup && $isInValidation)
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                No puedes validar este documento en esta etapa.
                            </small>
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
                @if(auth()->user()->canDocument('approve-documents'))
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#approveStageModal">
                        Aprobar
                    </button>
                @else
                    <button type="button" class="btn btn-primary" disabled title="No tienes permiso para aprobar documentos">
                        Aprobar
                    </button>
                @endif

                @if(auth()->user()->canDocument('reject-documents'))
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#rejectStageModal">
                        Rechazar
                    </button>
                @else
                    <button type="button" class="btn btn-outline-primary" disabled title="No tienes permiso para rechazar documentos">
                        Rechazar
                    </button>
                @endif
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

            <div class="d-flex justify-content-between align-items-center mb-3">
                <small class="fw-semibold text-dark">
                    <i class="fas fa-history me-2"></i>Historial de validación
                </small>
                <span class="badge bg-primary-subtle text-primary">{{ $validationHistory->count() }}</span>
            </div>

            <div class="validation-history-scroll">
                @foreach($validationHistory as $history)
                    <div class="validation-item border-bottom py-3 px-2">
                        <div class="d-flex align-items-start gap-2">
                            {{-- Icon --}}
                            @if($history->action === 'approved')
                                <div class="shrink-0">
                                    <i class="fas fa-check-circle text-success mt-1" style="font-size: 0.95rem;"></i>
                                </div>
                            @elseif($history->action === 'rejected')
                                <div class="shrink-0">
                                    <i class="fas fa-times-circle text-danger mt-1" style="font-size: 0.95rem;"></i>
                                </div>
                            @else
                                <div class="shrink-0">
                                    <i class="fas fa-undo text-warning mt-1" style="font-size: 0.95rem;"></i>
                                </div>
                            @endif

                            {{-- Content --}}
                            <div class="grow min-width-0">
                                {{-- Header --}}
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div>
                                        <small class="fw-semibold d-block text-dark">
                                            {{ $history->action === 'approved' ? '✓ Aprobado' : ($history->action === 'rejected' ? '✗ Rechazado' : '↻ Devuelto') }}
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-user me-1"></i>{{ $history->validator->full_name ?? 'Sistema' }}
                                        </small>
                                    </div>
                                    <small class="text-muted shrink-0" style="font-size: 0.75rem; white-space: nowrap; margin-left: auto;">
                                        <i class="fas fa-calendar-alt me-1"></i>{{ $history->validated_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>

                                {{-- Stage Info --}}
                                <small class="text-muted d-block mt-1">
                                    <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                        Etapa {{ $history->stage_number }} - {{ ucfirst($history->validator_group) }}
                                    </span>
                                </small>

                                {{-- Comments --}}
                                @if($history->comments)
                                    <div class="alert alert-light py-2 px-2 mt-2 mb-0" role="alert">
                                        <small class="text-dark d-block">
                                            <strong>Observaciones:</strong><br>
                                            {{ $history->comments }}
                                        </small>
                                    </div>
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
                        <small class="fw-semibold d-block">Sin historial de validación</small>
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
@include('documents::documents.documents.components.validation.modals.approve-stage')
@include('documents::documents.documents.components.validation.modals.reject-stage')
