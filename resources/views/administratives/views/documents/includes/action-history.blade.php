<!-- Document Action History -->
<div class="card mb-3">
    <div class="card-header p-3 bg-white border-bottom">
        <h5 class="mb-1 fw-bold">Historial de acciones</h5>
        <p class="small mb-0 text-muted">Registro completo de todas las acciones realizadas en este documento</p>
    </div>

    @if($document->actions && $document->actions->count() > 0)
        @php
            $actions = $document->actions->sortByDesc('created_at');
            $totalActions = $actions->count();
            $showLimit = 5;
            $hasMore = $totalActions > $showLimit;
        @endphp

        @if($hasMore)
            <div class="alert alert-info alert-sm mb-2" role="alert">
                <small><i class="fas fa-info-circle me-1"></i> Mostrando las últimas {{ $showLimit }} acciones de {{ $totalActions }} totales</small>
            </div>
        @endif

        <div class="comment-widgets scrollable common-widget action-history-scroll">
            @foreach($actions->take($showLimit) as $action)
                <div class="comment-row border-bottom px-2 px-md-3 py-2 py-md-3 action-item">


                    <!-- Action Content -->
                    <div class="comment-text w-100">
                        <!-- Header: Name and Action Type -->
                        <div class="d-flex align-items-flex-start justify-content-between gap-2 mb-2">
                            <div class="flex-grow-1 min-width-0">
                                <h6 class="fw-semibold mb-1 text-truncate small">
                                    @if($action->performed_by && $action->performer)
                                        {{ $action->performer->name }}
                                    @else
                                        Sistema
                                    @endif
                                </h6>
                                <small class="text-muted d-block text-truncate">{{ $action->action_name }}</small>
                            </div>
                            <span class="badge flex-shrink-0
                                @if($action->action_type === 'upload') bg-primary-subtle text-primary
                                @elseif($action->action_type === 'status_change') bg-warning-subtle text-warning
                                @elseif($action->action_type === 'email_sent') bg-info-subtle text-info
                                @elseif($action->action_type === 'approval') bg-success-subtle text-success
                                @elseif($action->action_type === 'rejection') bg-danger-subtle text-danger
                                @elseif($action->action_type === 'note') bg-secondary-subtle text-secondary
                                @else bg-light text-dark
                                @endif
                            " style="font-size: 0.7rem; white-space: nowrap;">
                                @if($action->action_type === 'upload')
                                    <i class="fas fa-upload me-1"></i><span class="d-none d-sm-inline">Carga</span>
                                @elseif($action->action_type === 'status_change')
                                    <i class="fas fa-exchange-alt me-1"></i><span class="d-none d-sm-inline">Estado</span>
                                @elseif($action->action_type === 'email_sent')
                                    <i class="fas fa-envelope me-1"></i><span class="d-none d-sm-inline">Email</span>
                                @elseif($action->action_type === 'approval')
                                    <i class="fas fa-check-circle me-1"></i><span class="d-none d-sm-inline">Aprobado</span>
                                @elseif($action->action_type === 'rejection')
                                    <i class="fas fa-times-circle me-1"></i><span class="d-none d-sm-inline">Rechazado</span>
                                @elseif($action->action_type === 'note')
                                    <i class="fas fa-sticky-note me-1"></i><span class="d-none d-sm-inline">Nota</span>
                                @else
                                    <i class="fas fa-circle me-1"></i><span class="d-none d-sm-inline">{{ ucfirst(str_replace('_', ' ', $action->action_type)) }}</span>
                                @endif
                            </span>
                        </div>

                        <!-- Description -->
                        @if($action->description)
                            <p class="mb-2 small text-dark text-truncate-2" style="line-height: 1.4;">
                                {{ $action->description }}
                            </p>
                        @endif

                        <!-- Action Footer -->
                        <div class="comment-footer mt-1">
                            <div class="d-flex flex-column flex-sm-row align-items-flex-start justify-content-between gap-1 gap-sm-2">
                                <div class="d-flex align-items-center gap-1 min-width-0 small">
                                    @if($action->performed_by && $action->performer)
                                        <i class="fas fa-user-circle text-muted flex-shrink-0"></i>
                                        <span class="text-muted text-truncate" style="font-size: 0.75rem;">
                                            {{ $action->performer->roles?->first()?->name ?? 'Usuario' }}
                                        </span>
                                    @else
                                        <i class="fas fa-cog text-muted flex-shrink-0"></i>
                                        <span class="text-muted" style="font-size: 0.75rem;">Sistema</span>
                                    @endif
                                </div>

                                <!-- Metadata Badges -->
                                @if($action->metadata && count($action->metadata) > 0)
                                    <div class="d-flex gap-1 flex-wrap justify-content-sm-end">
                                        @foreach($action->metadata as $key => $value)
                                            <span class="badge bg-light text-dark" style="font-size: 0.6rem; white-space: nowrap;">
                                                {{ ucfirst(str_replace('_', ' ', $key)) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Date -->
                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                {{ $action->created_at->format('d M Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-4 text-center">
            <div class="mb-3">
                <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
            </div>
            <p class="text-muted small mb-0">
                <strong>Sin acciones registradas</strong>
                <br>
                No hay acciones registradas para este documento aún.
            </p>
        </div>
    @endif
</div>

@push('styles')
<style>
    .action-history-scroll {
        overflow-y: auto;
        border-top: 1px solid #e9ecef;
        height: 300px;
    }

    @media (min-width: 576px) {
        .action-history-scroll {
            height: 350px;
        }
    }

    @media (min-width: 768px) {
        .action-history-scroll {
            height: 400px;
        }
    }

    @media (min-width: 992px) {
        .action-history-scroll {
            height: 450px;
        }
    }

    .action-history-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .action-history-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .action-history-scroll::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 4px;
    }

    .action-history-scroll::-webkit-scrollbar-thumb:hover {
        background: #999;
    }

    .action-item {
        display: flex;
        flex-direction: row;
        gap: 0.75rem;
        transition: all 0.2s ease;
        border-color: #e9ecef !important;
    }

    .action-item:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6 !important;
    }

    .action-avatar {
        min-width: 40px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .action-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .comment-text {
        overflow: hidden;
    }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .min-width-0 {
        min-width: 0;
    }

    /* Responsive Typography */
    @media (max-width: 575px) {
        .action-item {
            gap: 0.5rem;
        }

        .action-item h6 {
            font-size: 0.875rem;
            margin-bottom: 0.25rem !important;
        }

        .action-item .small {
            font-size: 0.75rem !important;
        }

        .comment-footer {
            font-size: 0.7rem;
        }
    }

    @media (min-width: 576px) {
        .action-item {
            gap: 0.75rem;
        }
    }
</style>
@endpush
