<div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm h-100 position-relative" style="overflow: hidden;">
        <!-- Background Accent -->
        <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background-color: rgba(144, 187, 19, 0.05); border-radius: 50%; z-index: 0;"></div>

        <div class="card-body p-4 position-relative" style="z-index: 1;">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3" style="background-color: rgba(144, 187, 19, 0.15);">
                        <i class="{{ $icon }}" style="color: #90bb13; font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">
                            @switch($syncType)
                                @case('product')
                                    Productos
                                    @break
                                @case('category')
                                    Categorías
                                    @break
                                @case('price')
                                    Precios
                                    @break
                                @case('provider')
                                    Proveedores
                                    @break
                            @endswitch
                        </h6>
                    </div>
                </div>
                <div>
                    <span class="badge" style="background-color:
                        @if($status?->status === 'completed')
                            #13C672
                        @elseif($status?->status === 'failed')
                            #FA896B
                        @elseif($status?->status === 'running')
                            #90bb13
                        @else
                            #FEC90F
                        @endif
                    ">
                        {{ ucfirst($status?->status ?? 'inactivo') }}
                    </span>
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <div class="p-2 rounded-3" style="background-color: #f9f9f9;">
                        <p class="text-muted small mb-1">Total</p>
                        <h6 class="fw-bold mb-0">{{ number_format($metrics['total_items'] ?? 0) }}</h6>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded-3" style="background-color: #f9f9f9;">
                        <p class="text-muted small mb-1">Sincronizado</p>
                        <h6 class="fw-bold mb-0" style="color: #13C672;">{{ number_format($metrics['synced_items'] ?? 0) }}</h6>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">Progreso</small>
                    <small class="fw-bold" style="color: #90bb13;">{{ $metrics['progress_percentage'] ?? 0 }}%</small>
                </div>
                <div class="progress" style="height: 8px; border-radius: 10px; background-color: #e9ecef;">
                    <div
                        class="progress-bar"
                        role="progressbar"
                        style="width: {{ $metrics['progress_percentage'] ?? 0 }}%; background-color: #90bb13; border-radius: 10px;"
                    ></div>
                </div>
            </div>

            <!-- Last Sync Info -->
            @if($status?->completed_at)
                <p class="text-muted small mb-3">
                    <i class="fas fa-clock me-1"></i>
                    Última: {{ $status->completed_at->diffForHumans() }}
                </p>
            @else
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Nunca sincronizado
                </p>
            @endif

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                @if($status?->status === 'running')
                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="cancelSync('{{ $syncType }}')">
                        <i class="fas fa-stop-circle me-1"></i>Detener
                    </button>
                @else
                    <button type="button" class="btn btn-sm btn-success" wire:click="triggerSync('{{ $syncType }}')">
                        <i class="fas fa-sync me-1"></i>Sincronizar ahora
                    </button>
                @endif

                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openDetails('{{ $syncType }}')">
                    <i class="fas fa-eye me-1"></i>Ver detalles
                </button>
            </div>

            <!-- Loading State -->
            <div wire:loading wire:target="triggerSync, cancelSync" class="position-absolute inset-0 d-flex align-items-center justify-content-center rounded-3" style="background-color: rgba(255, 255, 255, 0.8); z-index: 10;">
                <div class="spinner-border spinner-border-sm" style="color: #90bb13;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        </div>

        <!-- Bottom Accent Line -->
        <div style="height: 3px; background-color: #90bb13; border-radius: 0;"></div>
    </div>
</div>
