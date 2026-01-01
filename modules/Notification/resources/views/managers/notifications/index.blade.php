@extends('layouts.theme')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary">
                    <h4 class="mb-0 text-white">
                        <i class="fa fa-bell me-2"></i>Notificaciones
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Filter Tabs -->
                    <ul class="nav nav-pills mb-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $filter === 'all' ? 'active' : '' }}"
                               href="{{ route('manager.notifications', ['filter' => 'all']) }}">
                                <i class="fa fa-bell me-1"></i> Todas
                                <span class="badge bg-secondary rounded-pill ms-1">{{ $notifications->total() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $filter === 'unread' ? 'active' : '' }}"
                               href="{{ route('manager.notifications', ['filter' => 'unread']) }}">
                                <i class="fa fa-envelope me-1"></i> No leídas
                                <span class="badge bg-primary rounded-pill ms-1">{{ $unreadCount }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $filter === 'read' ? 'active' : '' }}"
                               href="{{ route('manager.notifications', ['filter' => 'read']) }}">
                                <i class="fa fa-envelope-open me-1"></i> Leídas
                            </a>
                        </li>
                    </ul>

                    <!-- Actions Bar -->
                    @if($unreadCount > 0)
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                            <span><i class="fa fa-info-circle me-2"></i>Tienes {{ $unreadCount }} notificación(es) sin leer</span>
                            <form action="{{ route('api.notifications.mark-all-read') }}" method="POST" style="display: inline;" class="mark-all-read-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fa fa-check-double me-1"></i> Marcar todas como leídas
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Notifications List -->
                    @if($notifications->count() > 0)
                        <div class="list-group">
                            @foreach($notifications as $notification)
                                @php
                                    $data = $notification->data;
                                    $isUnread = $notification->read_at === null;
                                    $colorClass = $data['color'] ?? 'primary';
                                    $iconClass = $data['icon'] ?? 'fa fa-bell';
                                    $actionUrl = $data['action_url'] ?? null;
                                @endphp

                                <div class="list-group-item {{ $isUnread ? 'list-group-item-action bg-primary bg-opacity-10 border-primary border-opacity-25' : '' }}">
                                    <div class="d-flex gap-3 align-items-start">
                                        <!-- Icon -->
                                        <div class="flex-shrink-0">
                                            <span class="bg-{{ $colorClass }}-subtle rounded-circle d-flex align-items-center justify-content-center text-{{ $colorClass }}" style="width: 48px; height: 48px;">
                                                <i class="{{ $iconClass }} fs-5"></i>
                                            </span>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                <h6 class="mb-0 fw-semibold">
                                                    {{ $data['title'] ?? 'Notificación' }}
                                                    @if($isUnread)
                                                        <span class="badge bg-primary ms-2" style="font-size: 10px;">NUEVO</span>
                                                    @endif
                                                </h6>
                                                <small class="text-muted text-nowrap">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>

                                            @if(isset($data['message']) && $data['message'])
                                                <p class="mb-3 text-muted">{{ $data['message'] }}</p>
                                            @endif

                                            <!-- Actions -->
                                            <div class="d-flex gap-2 flex-wrap">
                                                @if($actionUrl)
                                                    <a href="{{ $actionUrl }}" class="btn btn-sm btn-{{ $colorClass }}">
                                                        <i class="fa fa-eye me-1"></i> {{ $data['action_text'] ?? 'Ver' }}
                                                    </a>
                                                @endif

                                                @if($isUnread)
                                                    <button type="button" class="btn btn-sm btn-outline-success mark-as-read-btn" data-notification-id="{{ $notification->id }}">
                                                        <i class="fa fa-check me-1"></i> Marcar como leída
                                                    </button>
                                                @endif

                                                <button type="button" class="btn btn-sm btn-outline-danger delete-notification-btn" data-notification-id="{{ $notification->id }}">
                                                    <i class="fa fa-trash me-1"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fa fa-bell-slash fs-1 text-muted opacity-50"></i>
                            </div>
                            <h5 class="fw-semibold mb-2">No hay notificaciones</h5>
                            <p class="text-muted mb-0">
                                @if($filter === 'unread')
                                    No tienes notificaciones sin leer en este momento
                                @elseif($filter === 'read')
                                    No tienes notificaciones leídas
                                @else
                                    No tienes ninguna notificación
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Mark as read functionality
    $('.mark-as-read-btn').on('click', function() {
        const btn = $(this);
        const notificationId = btn.data('notification-id');
        const url = '{{ url('/api/notifications') }}/' + notificationId + '/read';

        $.ajax({
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error al marcar la notificación como leída');
            }
        });
    });

    // Delete notification functionality
    $('.delete-notification-btn').on('click', function() {
        if (!confirm('¿Estás seguro de que deseas eliminar esta notificación?')) {
            return;
        }

        const btn = $(this);
        const notificationId = btn.data('notification-id');
        const url = '{{ url('/api/notifications') }}/' + notificationId;

        $.ajax({
            url: url,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error al eliminar la notificación');
            }
        });
    });

    // Mark all as read functionality
    $('.mark-all-read-form').on('submit', function(e) {
        e.preventDefault();

        if (!confirm('¿Marcar todas las notificaciones como leídas?')) {
            return;
        }

        $.ajax({
            url: '{{ route('api.notifications.mark-all-read') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error al marcar todas las notificaciones como leídas');
            }
        });
    });
});
</script>
@endpush
