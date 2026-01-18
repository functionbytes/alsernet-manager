<div wire:ignore.self>
    <!-- Timeline -->
    <div class="p-4">
        @forelse($activities as $activity)
            <div class="d-flex mb-4 pb-4" @if(!$loop->last) style="border-bottom: 1px solid #e9ecef;" @endif wire:key="activity-{{ $activity->id }}">
                <!-- Timeline Marker -->
                <div class="me-4 flex-shrink-0">
                    <div
                        class="rounded-circle d-flex align-items-center justify-content-center"
                        style="
                            width: 44px;
                            height: 44px;
                            background-color:
                            @if($activity->status === 'completed')
                                #13C672
                            @elseif($activity->status === 'failed')
                                #FA896B
                            @elseif($activity->status === 'running')
                                #90bb13
                            @else
                                #FEC90F
                            @endif;
                            color: white;
                            font-weight: 600;
                        "
                    >
                        @switch($activity->sync_type)
                            @case('product')
                                <i class="fas fa-cube"></i>
                                @break
                            @case('category')
                                <i class="fas fa-sitemap"></i>
                                @break
                            @case('price')
                                <i class="fas fa-tag"></i>
                                @break
                            @case('provider')
                                <i class="fas fa-truck"></i>
                                @break
                            @default
                                <i class="fas fa-sync"></i>
                        @endswitch
                    </div>
                </div>

                <!-- Timeline Content -->
                <div class="flex-grow-1 min-w-0">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-bold mb-1">
                                {{ ucfirst($activity->sync_type) }}
                                @if($activity->supplier_id)
                                    <span class="text-muted fw-normal small ms-2">
                                        • {{ $activity->supplier?->name ?? 'Proveedor desconocido' }}
                                    </span>
                                @endif
                            </h6>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <small class="text-muted d-block">
                                {{ $activity->created_at->format('H:i') }}
                            </small>
                            <small class="text-muted">
                                {{ $activity->created_at->format('d M') }}
                            </small>
                        </div>
                    </div>

                    <!-- Status Badge & Stats -->
                    <div class="d-flex gap-2 align-items-center mb-2 flex-wrap">
                        <span
                            class="badge"
                            style="background-color:
                            @if($activity->status === 'completed')
                                #13C672
                            @elseif($activity->status === 'failed')
                                #FA896B
                            @elseif($activity->status === 'running')
                                #90bb13
                            @else
                                #FEC90F
                            @endif
                        ">
                            {{ ucfirst($activity->status) }}
                        </span>

                        <!-- Metrics -->
                        <small class="text-muted">
                            <i class="fas fa-check-circle" style="color: #13C672;"></i>
                            {{ number_format($activity->total_items ?? 0) }} procesados
                        </small>

                        @if($activity->failed_items > 0)
                            <small class="text-danger">
                                <i class="fas fa-times-circle"></i>
                                {{ number_format($activity->failed_items) }} fallidos
                            </small>
                        @endif

                        @if($activity->completed_at && $activity->created_at)
                            <small class="text-muted">
                                <i class="fas fa-clock"></i>
                                {{ $activity->created_at->diffInSeconds($activity->completed_at) }}s
                            </small>
                        @endif
                    </div>

                    <!-- Progress Bar (if running) -->
                    @if($activity->status === 'running')
                        <div class="progress mb-2" style="height: 6px; border-radius: 8px;">
                            <div
                                class="progress-bar"
                                role="progressbar"
                                style="width: {{ $activity->getProgressPercentage() ?? 0 }}%; background-color: #90bb13;"
                            ></div>
                        </div>
                        <small class="text-muted d-block">
                            {{ $activity->getProgressPercentage() ?? 0 }}% completado
                        </small>
                    @endif

                    <!-- Error Message (if failed) -->
                    @if($activity->status === 'failed' && $activity->error_message)
                        <div class="alert alert-danger alert-sm mt-2 mb-0 d-flex align-items-start gap-2" role="alert">
                            <i class="fas fa-exclamation-triangle flex-shrink-0 mt-1"></i>
                            <div>
                                <small>{{ Str::limit($activity->error_message, 100) }}</small>
                                @if(strlen($activity->error_message) > 100)
                                    <a href="#" class="small" wire:click.prevent="viewError({{ $activity->id }})">
                                        Ver más...
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-history mb-3 d-block" style="font-size: 2rem; opacity: 0.25;"></i>
                <p class="small">No hay actividades registradas aún</p>
            </div>
        @endforelse
    </div>

    <!-- Load More / Pagination -->
    @if($activities->count() > 0)
        <div class="p-4 border-top text-center">
            @if(count($activities) >= $perPage)
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="loadMore">
                    <i class="fas fa-download me-1"></i>Cargar más
                </button>
            @endif
        </div>
    @endif

    <!-- Clear Log (Admin only) -->
    @if(auth()->user()?->hasRole('super-admin'))
        <div class="p-4 border-top text-end">
            <button type="button" class="btn btn-sm btn-outline-danger" wire:click="clearLog" onclick="confirm('¿Está seguro de que desea limpiar el registro?') || event.preventDefault()">
                <i class="fas fa-trash me-1"></i>Limpiar registro
            </button>
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading wire:target="loadMore, clearLog" class="position-fixed bottom-0 end-0 p-3">
        <div class="spinner-border spinner-border-sm" style="color: #90bb13;">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>
</div>
