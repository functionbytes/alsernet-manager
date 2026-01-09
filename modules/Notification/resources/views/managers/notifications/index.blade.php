@extends('layouts.theme')

@section('page_title', 'Notificaciones')

@section('content')

    @include('theme.components.card', ['title' => 'Notificaciones'])

    <div class="widget-content searchable-container list">

        @include('theme.components.alerts')

        <div class="card">
            {{-- Header Section --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Mis notificaciones</h5>
                        <p class="small mb-0 text-muted">Administra y revisa todas tus notificaciones del sistema</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if($unreadCount > 0)
                            <button type="button" class="btn btn-primary mark-all-read-btn">
                                <i class="fas fa-check-double me-2"></i>Marcar todas como leídas
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    @php
                        $totalCount = $notifications->total();
                        $readCount = $totalCount - $unreadCount;
                        $todayCount = \App\Models\User::find(auth()->id())->notifications()->whereDate('created_at', today())->count();
                    @endphp
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-primary mb-2">Total</h6>
                                        <h4 class="mb-1 fw-bold">{{ $totalCount }}</h4>
                                        <small class="text-muted">Todas las notificaciones</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-warning mb-2">No leídas</h6>
                                        <h4 class="mb-1 fw-bold">{{ $unreadCount }}</h4>
                                        <small class="text-muted">Pendientes de revisar</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-success mb-2">Leídas</h6>
                                        <h4 class="mb-1 fw-bold">{{ $readCount }}</h4>
                                        <small class="text-muted">Ya revisadas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="card-title text-info mb-2">Hoy</h6>
                                        <h4 class="mb-1 fw-bold">{{ $todayCount }}</h4>
                                        <small class="text-muted">Recibidas hoy</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Tabs --}}
            <div class="card-body border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-semibold">Filtrar notificaciones</h6>
                    </div>
                    <div class="btn-group" role="group">
                        <a href="{{ route('notifications.index', ['filter' => 'all']) }}"
                           class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Todas
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                           class="btn btn-sm {{ $filter === 'unread' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            No leídas
                         </a>
                        <a href="{{ route('notifications.index', ['filter' => 'read']) }}"
                           class="btn btn-sm {{ $filter === 'read' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Leídas
                        </a>
                    </div>
                </div>
            </div>

            {{-- Notifications List --}}
            <div class="card-body">
                @if($notifications->count() > 0)
                    <div class="notifications-list">
                        @foreach($notifications as $notification)
                            @php
                                $data = $notification->data;
                                $isUnread = $notification->read_at === null;
                                $colorClass = $data['color'] ?? 'primary';
                                $iconClass = $data['icon'] ?? 'fas fa-bell';
                                $actionUrl = $data['action_url'] ?? null;
                            @endphp

                            <div class="notification-item card mb-3 {{ $isUnread ? 'border-primary' : 'border' }}" data-notification-id="{{ $notification->id }}">
                                <div class="card-body">
                                    <div class="d-flex gap-3 align-items-start">
                                        {{-- Icon --}}
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center {{ $isUnread ? 'bg-primary' : 'bg-light-secondary' }}"
                                                 style="width: 48px; height: 48px;">
                                                <i class="{{ $iconClass }} fs-5 {{ $isUnread ? 'text-white' : 'text-muted' }}"></i>
                                            </div>
                                        </div>

                                        {{-- Content --}}
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <div>
                                                    <h6 class="mb-1 fw-bold">
                                                        {{ $data['title'] ?? 'Notificación' }}
                                                    </h6>
                                                    @if($isUnread)
                                                        <span class="badge bg-primary-subtle text-primary" style="font-size: 10px;">
                                                            <i class="fas fa-circle me-1" style="font-size: 6px;"></i>NUEVO
                                                        </span>
                                                    @endif
                                                </div>
                                                <small class="text-muted text-nowrap">
                                                    <i class="fas fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                                </small>
                                            </div>

                                            @if(isset($data['message']) && $data['message'])
                                                <p class="mb-3 text-muted">{{ $data['message'] }}</p>
                                            @endif

                                            {{-- Actions --}}
                                            <div class="d-flex gap-2 flex-wrap">
                                                @if($actionUrl)
                                                    <a href="{{ $actionUrl }}" target="_blank" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-external-link-alt me-1"></i>{{ $data['action_text'] ?? 'Ver detalles' }}
                                                    </a>
                                                @endif

                                                @if($isUnread)
                                                    <button type="button" class="btn btn-sm btn-outline-success mark-as-read-btn">
                                                        <i class="fas fa-check me-1"></i>Marcar como leída
                                                    </button>
                                                @endif

                                                <button type="button" class="btn btn-sm btn-outline-danger delete-notification-btn">
                                                    <i class="fas fa-trash me-1"></i>Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <div class="round-48 rounded-circle bg-light-subtle text-muted mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-bell-slash fs-8"></i>
                            </div>
                            <h6 class="mb-1">No hay notificaciones</h6>
                            <p class="text-muted mb-3">
                                @if($filter === 'unread')
                                    No tienes notificaciones sin leer en este momento
                                @elseif($filter === 'read')
                                    No tienes notificaciones leídas
                                @else
                                    No tienes ninguna notificación
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Pagination --}}
            @if($notifications->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando <strong>{{ $notifications->firstItem() }}</strong> a <strong>{{ $notifications->lastItem() }}</strong>
                            de <strong>{{ $notifications->total() }}</strong> notificaciones
                        </div>
                        <nav aria-label="Page navigation">
                            {{ $notifications->links() }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('styles')
<style>
    .notification-item {
        transition: all 0.2s ease;
    }

    .notification-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .notification-item.border-primary {
        background-color: rgba(144, 187, 19, 0.02);
    }

    .notification-item.border-primary:hover {
        background-color: rgba(144, 187, 19, 0.05);
        border-color: #90bb13 !important;
        box-shadow: 0 4px 12px rgba(144, 187, 19, 0.2);
    }

    .stat-card {
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Mark as read functionality (individual)
    $('.mark-as-read-btn').on('click', function() {
        const btn = $(this);
        const notificationCard = btn.closest('.notification-item');
        const notificationId = notificationCard.data('notification-id');
        const url = '{{ url('/api/notifications') }}/' + notificationId + '/read';

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Procesando...');

        $.ajax({
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success('Notificación marcada como leída', 'Éxito', {
                    positionClass: 'toast-bottom-right'
                });

                // Remove the card with animation
                notificationCard.fadeOut(300, function() {
                    $(this).remove();

                    // If no notifications left, reload page to show empty state
                    if ($('.notification-item').length === 0) {
                        setTimeout(() => location.reload(), 500);
                    }
                });
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Marcar como leída');
                toastr.error('Error al marcar la notificación como leída', 'Error', {
                    positionClass: 'toast-bottom-right'
                });
            }
        });
    });

    // Delete notification functionality
    $('.delete-notification-btn').on('click', function() {
        const btn = $(this);
        const notificationCard = btn.closest('.notification-item');
        const notificationId = notificationCard.data('notification-id');
        const url = '{{ url('/api/notifications') }}/' + notificationId;

        // Show confirmation with toastr-like style (using native confirm for now)
        if (!confirm('¿Estás seguro de que deseas eliminar esta notificación?')) {
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Eliminando...');

        $.ajax({
            url: url,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success('Notificación eliminada correctamente', 'Éxito', {
                    positionClass: 'toast-bottom-right'
                });

                // Remove the card with animation
                notificationCard.fadeOut(300, function() {
                    $(this).remove();

                    // If no notifications left, reload page to show empty state
                    if ($('.notification-item').length === 0) {
                        setTimeout(() => location.reload(), 500);
                    }
                });
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-trash me-1"></i>Eliminar');
                toastr.error('Error al eliminar la notificación', 'Error', {
                    positionClass: 'toast-bottom-right'
                });
            }
        });
    });

    // Mark all as read functionality
    $('.mark-all-read-btn').on('click', function() {
        const btn = $(this);

        if (!confirm('¿Marcar todas las notificaciones como leídas?')) {
            return;
        }

        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Procesando...');

        $.ajax({
            url: '{{ route('api.notifications.mark-all-read') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success('Todas las notificaciones han sido marcadas como leídas', 'Éxito', {
                    positionClass: 'toast-bottom-right'
                });

                setTimeout(() => location.reload(), 1000);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalHtml);
                toastr.error('Error al marcar todas las notificaciones como leídas', 'Error', {
                    positionClass: 'toast-bottom-right'
                });
            }
        });
    });

    // Show session messages
    @if (session('success'))
        toastr.success('{{ session('success') }}', 'Éxito', {
            positionClass: 'toast-bottom-right'
        });
    @endif

    @if (session('error'))
        toastr.error('{{ session('error') }}', 'Error', {
            positionClass: 'toast-bottom-right'
        });
    @endif
});
</script>
@endpush
