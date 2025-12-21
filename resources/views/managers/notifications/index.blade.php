@extends('layouts.managers')

@section('content')

@include('managers.includes.card', ['title' => 'Notificaciones'])

<div class="card">
    <div class="card-body">

        <!-- Filter Tabs -->
        <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('manager.notifications.index', ['filter' => 'all']) }}">
                    <i class="fas fa-bell me-1"></i> Todas
                    <span class="badge bg-secondary rounded-pill ms-1">{{ $notifications->total() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $filter === 'unread' ? 'active' : '' }}" href="{{ route('manager.notifications.index', ['filter' => 'unread']) }}">
                    <i class="fas fa-envelope me-1"></i> No leídas
                    <span class="badge bg-primary rounded-pill ms-1">{{ $unreadCount }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $filter === 'read' ? 'active' : '' }}" href="{{ route('manager.notifications.index', ['filter' => 'read']) }}">
                    <i class="fas fa-envelope-open me-1"></i> Leídas
                </a>
            </li>
        </ul>

        <!-- Actions Bar -->
        @if($unreadCount > 0)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">{{ $unreadCount }} notificación(es) sin leer</span>
            <form action="{{ route('manager.user-notifications.mark-all-read') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('¿Marcar todas las notificaciones como leídas?')">
                    <i class="fas fa-check-double me-1"></i> Marcar todas como leídas
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
                        $iconClass = $data['icon'] ?? 'fas fa-bell';
                        $actionUrl = $data['action_url'] ?? '#';
                    @endphp

                    <div class="list-group-item list-group-item-action {{ $isUnread ? 'bg-light-subtle' : '' }}">
                        <div class="d-flex gap-3 align-items-start">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <span class="bg-{{ $colorClass }}-subtle rounded-circle d-flex align-items-center justify-content-center text-{{ $colorClass }}" style="width: 45px; height: 45px;">
                                    <i class="{{ $iconClass }} fs-5"></i>
                                </span>
                            </div>

                            <!-- Content -->
                            <div class="flex-grow-1" style="min-width: 0;">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                    <h6 class="mb-0 fw-semibold">
                                        {{ $data['title'] ?? 'Notificación' }}
                                        @if($isUnread)
                                            <span class="badge bg-primary rounded-pill ms-2" style="font-size: 9px;">Nuevo</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted text-nowrap">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>

                                <p class="mb-2 text-muted">{{ $data['message'] ?? '' }}</p>

                                <!-- Actions -->
                                <div class="d-flex gap-2">
                                    @if($actionUrl && $actionUrl !== '#')
                                        <a href="{{ $actionUrl }}" class="btn btn-sm btn-{{ $colorClass }}">
                                            <i class="fas fa-eye me-1"></i> {{ $data['action_text'] ?? 'Ver' }}
                                        </a>
                                    @endif

                                    @if($isUnread)
                                        <form action="{{ route('manager.user-notifications.mark-as-read', $notification->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-check me-1"></i> Marcar como leída
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('manager.user-notifications.destroy', $notification->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta notificación?')">
                                            <i class="fas fa-trash me-1"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fs-1 text-muted mb-3 d-block"></i>
                <h5 class="text-muted">No hay notificaciones</h5>
                <p class="text-muted mb-0">
                    @if($filter === 'unread')
                        No tienes notificaciones sin leer
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

@endsection
