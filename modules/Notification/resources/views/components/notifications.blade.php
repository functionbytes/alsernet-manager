<!-- Notifications Bell Dropdown -->
<li class="nav-item nav-icon-hover-bg rounded-circle dropdown"
    id="notifications-dropdown"
    data-api-index-route="{{ route('api.notifications.index') }}"
    data-api-read-route="{{ url('/api/notifications/{id}/read') }}"
    data-mark-all-read-route="{{ route('api.notifications.mark-all-read') }}"
    data-refresh-interval="60000"
    data-limit="4">

    <a class="nav-link position-relative" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell fs-6"></i>
        <div id="notification-badge" class="position-absolute bg-primary rounded-circle" style="display: none;"></div>
    </a>

    <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2" style="min-width: 360px; max-width: 400px;">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
            <h5 class="mb-0 fs-5 fw-semibold">Notificaciones</h5>
            <span id="unread-count-text" class="badge bg-primary px-2 py-1" style="display: none;">0 nuevas</span>
        </div>

        <!-- Loading State -->
        <div id="notifications-loading" class="text-center py-5">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="text-muted mt-2 mb-0 small">Cargando notificaciones...</p>
        </div>

        <!-- Notifications List -->
        <div id="notifications-list" class="message-body" data-simplebar style="display: none;">
            <!-- Notifications will be inserted here via AJAX -->
        </div>

        <!-- Empty State -->
        <div id="notifications-empty" class="text-center py-5" style="display: none;">
            <div class="mb-3">
                <i class="fas fa-bell-slash fs-1 text-muted opacity-50"></i>
            </div>
            <h6 class="fw-semibold mb-1">Sin notificaciones</h6>
            <p class="text-muted mb-0 small">No tienes notificaciones nuevas</p>
        </div>

        <!-- Footer -->
        <div class="border-top p-3">
            <a href="{{ route('notifications.index') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                <div class="text-start">
                    <div class="fw-semibold">Ver todas las notificaciones</div>
                </div>
            </a>
        </div>
    </div>
</li>

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/Notification/css/notifications.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('modules/Notification/js/notifications.js') }}"></script>
@endpush
