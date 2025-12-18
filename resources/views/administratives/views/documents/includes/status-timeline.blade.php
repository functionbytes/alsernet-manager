<!-- Document Status Timeline -->
<div class="card mb-3">
    <div class="card-header p-3 bg-white border-bottom">
        <h5 class="mb-1 fw-bold" style="font-size: 1rem;">Historial de estado</h5>
        <p class="small mb-0 text-muted" style="font-size: 0.75rem;">Transiciones del documento</p>
    </div>
    <div class="card-body p-2">

        @if($document->statusHistories && $document->statusHistories->count() > 0)
            @php
                $histories = $document->statusHistories->sortByDesc('created_at');
                $totalHistories = $histories->count();
                $showLimit = 5;
                $hasMore = $totalHistories > $showLimit;
            @endphp

            @if($hasMore)
                <div class="alert alert-info py-1 px-2 mb-2" role="alert" style="font-size: 0.7rem;">
                    <i class="fas fa-info-circle me-1"></i> Últimos {{ $showLimit }}/{{ $totalHistories }}
                </div>
            @endif

            <div class="status-timeline-scroll @if($hasMore) scrollable @endif" @if($hasMore) style="max-height: 350px; overflow-y: auto;" @endif>
                <ul class="timeline-widget mb-0 position-relative">
                    @foreach($histories->take($showLimit) as $history)
                    <li class="timeline-item d-flex position-relative overflow-hidden pb-2">
                        <!-- Timeline Time -->
                        <div class="timeline-time text-muted flex-shrink-0 text-end pe-2" style="min-width: 50px; font-size: 0.65rem; line-height: 1.2;">
                            {{ $history->created_at->format('H:i') }}
                            <br>
                            <span style="font-size: 0.6rem;">{{ $history->created_at->format('d/m') }}</span>
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
                        <div class="timeline-desc ps-2 flex-grow-1">
                            <div class="mb-1">
                                <div class="d-flex align-items-center gap-1 flex-wrap" style="font-size: 0.7rem;">
                                    <span class="text-muted text-truncate" style="max-width: 60px;">{{ Str::limit($history->fromStatus->name ?? 'N/A', 10) }}</span>
                                    <i class="fas fa-arrow-right text-muted" style="font-size: 0.6rem;"></i>
                                    <span class="badge
                                        @if($history->toStatus->slug === 'pendiente') bg-secondary-subtle text-secondary
                                        @elseif($history->toStatus->slug === 'en-revision') bg-warning-subtle text-warning
                                        @elseif($history->toStatus->slug === 'aprobado') bg-success-subtle text-success
                                        @elseif($history->toStatus->slug === 'rechazado') bg-danger-subtle text-danger
                                        @else bg-info-subtle text-info
                                        @endif
                                        py-0 px-1" style="font-size: 0.65rem;">
                                        {{ Str::limit($history->toStatus->name, 15) }}
                                    </span>
                                </div>
                            </div>

                            <!-- User Info -->
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($history->changedBy->name ?? 'Sistema') }}&background=0d6efd&color=fff&size=20"
                                     alt="user" width="20" height="20" class="rounded-circle flex-shrink-0">
                                <div class="overflow-hidden">
                                    <div class="fw-semibold text-truncate" style="font-size: 0.65rem; line-height: 1.2;">{{ $history->changedBy->name ?? 'Sistema' }}</div>
                                    <div class="text-muted text-truncate" style="font-size: 0.6rem; line-height: 1.1;">{{ $history->changedBy->roles?->first()?->name ?? 'Usuario' }}</div>
                                </div>
                            </div>

                            <!-- Reason -->
                            @if($history->reason)
                                <div class="alert alert-light border-start border-2 border-primary py-1 px-2 mb-1" style="font-size: 0.65rem; line-height: 1.3;">
                                    <strong class="d-block mb-1">Razón:</strong>
                                    <span class="text-dark">{{ Str::limit($history->reason, 100) }}</span>
                                </div>
                            @endif

                            <!-- Metadata -->
                            @if($history->metadata && count($history->metadata) > 0)
                                <div class="text-muted" style="font-size: 0.6rem;">
                                    <i class="fas fa-circle-info me-1"></i>
                                    @foreach($history->metadata as $key => $value)
                                        <span class="badge bg-light text-dark py-0 px-1 me-1" style="font-size: 0.6rem;">
                                            {{ Str::limit(ucfirst(str_replace('_', ' ', $key)), 10) }}: {{ Str::limit(is_array($value) ? implode(', ', $value) : $value, 10) }}
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
            <div class="alert bg-light-subtle py-2 px-2 mb-0" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas fa-circle-info text-black me-1 mt-1" style="font-size: 0.7rem;"></i>
                    <div>
                        <div class="fw-semibold" style="font-size: 0.7rem;">Sin cambios</div>
                        <div class="text-muted" style="font-size: 0.65rem; line-height: 1.2;">No hay historial registrado</div>
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
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        position: relative;
        z-index: 2;
    }

    .timeline-badge-border {
        width: 1.5px;
        height: 30px;
        background: #e9ecef;
        position: relative;
        margin-top: -3px;
        margin-bottom: -3px;
    }

    .timeline-time {
        line-height: 1.2;
    }

    .timeline-desc {
        transition: all 0.15s ease;
    }

    .timeline-item:hover .timeline-desc {
        background-color: #f8f9fa;
        border-radius: 3px;
        padding: 0.25rem;
        margin-left: -0.25rem;
    }

    .timeline-badge-wrap {
        position: relative;
        z-index: 1;
    }

    .status-timeline-scroll {
        border-radius: 3px;
        padding-right: 0.25rem;
    }

    .status-timeline-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .status-timeline-scroll::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 3px;
    }

    .status-timeline-scroll::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 3px;
    }

    .status-timeline-scroll::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }
</style>
@endpush
