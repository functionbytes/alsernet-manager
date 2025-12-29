{{-- Modal: Aprobar etapa actual --}}
<div class="modal fade" id="approveStageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold mb-1">
                        Aprobar etapa actual
                    </h5>
                    <p class="text-muted small mb-0">Confirma la aprobación para avanzar en el flujo</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Current Stage Info --}}
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle" style="width: 48px; height: 48px;">
                        <i class="fas fa-layer-group text-primary fs-5"></i>
                    </div>
                    <div>
                        <p class="mb-0 fw-semibold">Etapa {{ $document->current_stage }} de {{ $document->total_stages }}</p>
                        <small class="text-muted">Grupo: {{ ucfirst($document->current_validator_group ?? 'Sin asignar') }}</small>
                    </div>
                </div>

                @if($document->current_stage < $document->total_stages)
                    @php
                        $nextStage = $document->current_stage + 1;
                        $stages = $document->getValidationWorkflowStages();
                        $nextGroup = $stages[$nextStage - 1] ?? null;
                        $nextGroupModel = $nextGroup ? \App\Models\Validation\ValidatorGroup::findByKey($nextGroup) : null;
                        $nextGroupUsers = $nextGroupModel ? $nextGroupModel->users : collect();
                    @endphp

                    {{-- Next Stage Preview --}}
                    <div class="alert bg-primary-subtle border-0 mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-arrow-right text-primary me-2 mt-1"></i>
                            <div>
                                <small class="fw-semibold d-block text-primary">Siguiente etapa</small>
                                <small class="text-dark">{{ ucfirst($nextGroup) }} - Se notificará al grupo o usuario asignado</small>
                            </div>
                        </div>
                    </div>

                    @if($nextGroupUsers->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Asignar a usuario específico
                        </label>
                        <select class="form-select form-select-sm" id="assignToUser">
                            <option value="">Todo el grupo ({{ $nextGroupUsers->count() }} usuarios)</option>
                            @foreach($nextGroupUsers as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->full_name }}
                                    @if($user->pivot->priority === 'primary') (Primario) @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>Opcional: asigna a una persona o deja que todo el grupo lo vea
                        </small>
                    </div>
                    @endif
                @else
                    {{-- Final Stage --}}
                    <div class="alert bg-primary-subtle border-0 mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-check-double text-primary me-2 mt-1"></i>
                            <div>
                                <small class="fw-semibold d-block text-primary">Última etapa</small>
                                <small class="text-dark">El documento será marcado como completamente aprobado</small>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mb-0">
                    <label class="form-label fw-semibold small">Comentarios <span class="text-muted fw-normal">(opcional)</span></label>
                    <textarea class="form-control form-control-sm" id="approveStageComments" rows="3"
                              placeholder="Agregar comentarios sobre la aprobación..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-primary w-100 mb-1" id="btnConfirmApproveStage">
                    Aprobar
                </button>
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // ===== Handler: Aprobar Etapa Actual =====
        $('#btnConfirmApproveStage').on('click', function() {
            const $btn = $(this);
            const comments = $('#approveStageComments').val();
            const assignedUserId = $('#assignToUser').val();

            // Deshabilitar botón y mostrar loading
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Aprobando...');

            $.ajax({
                url: "{{ route('administrative.documents.approve-stage', $document->uid) }}",
                method: 'POST',
                data: {
                    comments: comments,
                    assigned_user_id: assignedUserId || null,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, 'Éxito', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                        // Cerrar modal y recargar página después de 1.5s
                        $('#approveStageModal').modal('hide');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error al aprobar la etapa';
                    toastr.error(message, 'Error', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-bottom-right"
                    });

                    // Restaurar botón
                    $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Aprobar y Continuar');
                }
            });
        });

        // Limpiar modal al cerrar
        $('#approveStageModal').on('hidden.bs.modal', function() {
            $('#approveStageComments').val('');
            $('#assignToUser').val('');
            $('#btnConfirmApproveStage')
                .prop('disabled', false)
                .html('<i class="fas fa-check-circle me-2"></i>Aprobar y Continuar');
        });
    });
</script>
@endpush
