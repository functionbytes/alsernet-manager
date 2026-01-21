<div class="card border-0 shadow-sm h-100">
    <div class="card-body p-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h6 class="fw-bold mb-1">{{ ucfirst($syncType) }}</h6>
                <p class="text-muted small mb-0">
                    <span class="badge" style="background-color:
                        @if($status === 'completed')
                            #13C672
                        @elseif($status === 'failed')
                            #FA896B
                        @elseif($status === 'running')
                            #90bb13
                        @else
                            #FEC90F
                        @endif
                    ">
                        {{ ucfirst($status) }}
                    </span>
                </p>
            </div>
            <div class="text-end" wire:loading.class="opacity-50" wire:target="updateProgress">
                <p class="text-muted small mb-1">{{ $currentPhase ?? 'Preparando...' }}</p>
                <h5 class="fw-bold mb-0" style="color: #90bb13;">{{ $percentComplete ?? 0 }}%</h5>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress mb-4" style="height: 8px; border-radius: 10px; background-color: #f0f0f0;">
            <div
                class="progress-bar transition-all"
                role="progressbar"
                style="width: {{ $percentComplete ?? 0 }}%; background-color: #90bb13; transition: width 0.3s ease;"
                wire:ignore
                x-data="{ width: {{ $percentComplete ?? 0 }} }"
                @sync-progress-updated.window="width = $event.detail.percentComplete; $el.style.width = width + '%'"
            ></div>
        </div>

        <!-- Metrics -->
        <div class="row g-3 mb-3">
            <div class="col-6">
                <div class="p-3 rounded-3" style="background-color: rgba(144, 187, 19, 0.08);">
                    <p class="text-muted small mb-1">Procesados</p>
                    <h6 class="fw-bold mb-0">{{ number_format($processedItems ?? 0) }}/{{ number_format($totalItems ?? 0) }}</h6>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 rounded-3" style="background-color: rgba(250, 137, 107, 0.08);">
                    <p class="text-muted small mb-1">Fallidos</p>
                    <h6 class="fw-bold mb-0 text-danger">{{ number_format($failedItems ?? 0) }}</h6>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div wire:loading wire:target="updateProgress, handleCompletion, handleFailure" class="d-flex align-items-center gap-2 text-muted small">
            <div class="spinner-border spinner-border-sm" role="status" style="color: #90bb13;">
                <span class="visually-hidden">Cargando...</span>
            </div>
            Actualizando...
        </div>

        <!-- Completion Message -->
        @if($status === 'completed')
            <div class="alert alert-success alert-sm mb-0 d-flex align-items-center gap-2" role="alert">
                <i class="fas fa-check-circle"></i>
                <span class="small">Sincronización completada exitosamente</span>
            </div>
        @endif

        <!-- Error Message -->
        @if($status === 'failed')
            <div class="alert alert-danger alert-sm mb-0 d-flex align-items-center gap-2" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span class="small">Error durante la sincronización</span>
            </div>
        @endif
    </div>
</div>
