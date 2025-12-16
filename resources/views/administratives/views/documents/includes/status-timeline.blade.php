<!-- Document Status Timeline -->
<div class="card mb-3">
    <div class="card-header p-3 bg-white border-bottom">
        <h5 class="mb-1 fw-bold">Historial de cambios de estado</h5>
        <p class="small mb-0 text-muted">Auditoría completa de transiciones de estado en el documento</p>
    </div>
    <div class="card-body">

        @if($document->statusHistories && $document->statusHistories->count() > 0)
            @php
                $histories = $document->statusHistories->sortByDesc('created_at');
                $totalHistories = $histories->count();
                $showLimit = 5;
                $hasMore = $totalHistories > $showLimit;
            @endphp

            @if($hasMore)
                <div class="alert alert-info alert-sm mb-2" role="alert">
                    <small><i class="fas fa-info-circle me-1"></i> Mostrando los últimos {{ $showLimit }} cambios de {{ $totalHistories }} totales</small>
                </div>
            @endif

            <div class="status-timeline-scroll @if($hasMore) scrollable @endif" @if($hasMore) style="max-height: 450px; overflow-y: auto;" @endif>
                <ul class="timeline-widget mb-0 position-relative">
                    @foreach($histories->take($showLimit) as $history)
                    <li class="timeline-item d-flex position-relative overflow-hidden pb-3">
                        <!-- Timeline Time -->
                        <div class="timeline-time text-muted flex-shrink-0 text-end pe-3" style="min-width: 70px; font-size: 0.8rem;">
                            {{ $history->created_at->format('H:i') }}
                            <br>
                            <small>{{ $history->created_at->format('d/m/Y') }}</small>
                        </div>

                        <!-- Timeline Badge -->
                        <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                            <span class="timeline-badge
                                @if($history->toStatus->slug === 'pendiente') bg-secondary
                                @elseif($history->toStatus->slug === 'en-revision') bg-warning
                                @elseif($history->toStatus->slug === 'aprobado') bg-success
                                @elseif($history->toStatus->slug === 'rechazado') bg-danger
                                @else bg-info
                                @endif
                                flex-shrink-0">
                            </span>
                            @if(!$loop->last)
                                <span class="timeline-badge-border d-block flex-shrink-0"></span>
                            @endif
                        </div>

                        <!-- Timeline Description -->
                        <div class="timeline-desc ps-3 flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <span class="fw-semibold text-dark">
                                        {{ $history->fromStatus->name ?? 'N/A' }}
                                        <i class="fas fa-arrow-right text-muted mx-2" style="font-size: 0.8rem;"></i>
                                        <span class="badge
                                            @if($history->toStatus->slug === 'pendiente') bg-secondary-subtle text-secondary
                                            @elseif($history->toStatus->slug === 'en-revision') bg-warning-subtle text-warning
                                            @elseif($history->toStatus->slug === 'aprobado') bg-success-subtle text-success
                                            @elseif($history->toStatus->slug === 'rechazado') bg-danger-subtle text-danger
                                            @else bg-info-subtle text-info
                                            @endif
                                        ">
                                            {{ $history->toStatus->name }}
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <!-- User Info -->
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($history->changedBy->name ?? 'Sistema') }}&background=0d6efd&color=fff&size=32"
                                     alt="user" width="32" class="rounded-circle flex-shrink-0" style="font-size: 0.75rem;">
                                <div>
                                    <small class="fw-semibold d-block">{{ $history->changedBy->name ?? 'Sistema' }}</small>
                                    <small class="text-muted">{{ $history->changedBy->roles?->first()?->name ?? 'Usuario' }}</small>
                                </div>
                            </div>

                            <!-- Reason -->
                            @if($history->reason)
                                <div class="alert alert-light border-start border-4 border-primary py-2 px-3 mb-2">
                                    <small class="text-dark" style="line-height: 1.5;">
                                        <strong>Razón:</strong> {{ $history->reason }}
                                    </small>
                                </div>
                            @endif

                            <!-- Metadata -->
                            @if($history->metadata && count($history->metadata) > 0)
                                <div class="small text-muted">
                                    <i class="fas fa-circle-info me-1"></i>
                                    @foreach($history->metadata as $key => $value)
                                        <span class="badge bg-light text-dark me-1" style="font-size: 0.7rem;">
                                            {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_array($value) ? implode(', ', $value) : $value }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="alert bg-light-subtle py-3 px-3 mb-0" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas fa-circle-info text-black me-2 mt-1" style="font-size: 0.9rem;"></i>
                    <div>
                        <small class="fw-semibold d-block">Sin cambios de estado</small>
                        <small class="text-muted">Este documento aún no ha tenido cambios de estado registrados.</small>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .timeline-widget {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }

    .timeline-item {
        margin-bottom: 0;
    }

    .timeline-badge {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
        position: relative;
        z-index: 2;
    }

    .timeline-badge-border {
        width: 2px;
        height: 40px;
        background: #e9ecef;
        position: relative;
        margin-top: -5px;
        margin-bottom: -5px;
    }

    .timeline-time {
        line-height: 1.3;
    }

    .timeline-desc {
        transition: all 0.2s ease;
    }

    .timeline-item:hover .timeline-desc {
        background-color: #f8f9fa;
        border-radius: 4px;
        padding: 0.5rem;
        margin-left: -0.5rem;
    }

    .timeline-badge-wrap {
        position: relative;
        z-index: 1;
    }

    .status-timeline-scroll {
        border-radius: 4px;
        padding-right: 0.5rem;
    }

    .status-timeline-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .status-timeline-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .status-timeline-scroll::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 4px;
    }

    .status-timeline-scroll::-webkit-scrollbar-thumb:hover {
        background: #999;
    }
</style>
@endpush
