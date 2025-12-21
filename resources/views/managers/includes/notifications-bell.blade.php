<!-- Notifications Bell Dropdown -->
<li class="nav-item nav-icon-hover-bg rounded-circle dropdown" id="notifications-dropdown">
    <a class="nav-link position-relative" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell fs-6"></i>
        <div id="notification-badge" class="notification bg-primary rounded-circle" style="display: none;"></div>
    </a>
    <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2" style="min-width: 360px; max-width: 400px;">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
            <h5 class="mb-0 fs-5 fw-semibold">Notificaciones</h5>
            <span id="unread-count-text" class="badge bg-primary rounded-pill px-2 py-1" style="display: none; font-size: 11px;">0 nuevas</span>
        </div>

        <!-- Loading State -->
        <div id="notifications-loading" class="text-center py-5">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="text-muted mt-2 mb-0 small">Cargando notificaciones...</p>
        </div>

        <!-- Notifications List -->
        <div id="notifications-list" class="message-body" data-simplebar style="max-height: 320px; display: none;">
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
        <div class="border-top bg-light">
            <a href="{{ route('manager.notifications.index') }}" class="btn btn-primary w-100 rounded-0 rounded-bottom py-3 d-flex align-items-center justify-content-center gap-2">
                <i class="fas fa-inbox fs-5"></i>
                <div class="text-start">
                    <div class="fw-semibold">Ver todas las notificaciones</div>
                    <small id="total-notifications-text" class="opacity-75" style="font-size: 11px; display: none;">Tienes más notificaciones</small>
                </div>
            </a>
        </div>
    </div>
</li>

@push('scripts')
<script>
    $(document).ready(function() {
        let unreadCount = 0;

        // Cargar notificaciones al inicio
        loadNotifications();

        // Recargar notificaciones cada 60 segundos
        setInterval(loadNotifications, 60000);

        // Función para cargar notificaciones (solo las 4 últimas no leídas)
        function loadNotifications() {
            $.ajax({
                url: '/api/notifications?limit=4&unread=true',
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    unreadCount = response.unread_count;
                    updateBadge(unreadCount);
                    renderNotifications(response.notifications);

                    // Ocultar loading
                    $('#notifications-loading').hide();

                    // Actualizar contador en el header
                    if (unreadCount > 0) {
                        $('#unread-count-text').text(unreadCount + (unreadCount > 1 ? ' nuevas' : ' nueva')).show();
                    } else {
                        $('#unread-count-text').hide();
                    }

                    // Mostrar indicador si hay más notificaciones
                    const $totalText = $('#total-notifications-text');
                    if (unreadCount > 4) {
                        const remaining = unreadCount - 4;
                        $totalText.text(`+${remaining} notificación${remaining > 1 ? 'es' : ''} adicional${remaining > 1 ? 'es' : ''}`).show();
                    } else if (unreadCount > 0) {
                        $totalText.text('Ver historial completo').show();
                    } else {
                        $totalText.hide();
                    }
                },
                error: function(xhr) {
                    console.error('Error loading notifications:', xhr);
                    $('#notifications-loading').html(
                        '<div class="text-center py-5">' +
                        '<i class="fas fa-exclamation-triangle text-warning fs-1 mb-3"></i>' +
                        '<p class="text-muted mb-0 small">Error al cargar notificaciones</p>' +
                        '</div>'
                    );
                }
            });
        }

        // Actualizar badge del contador
        function updateBadge(count) {
            const badge = $('#notification-badge');
            if (count > 0) {
                badge.text(count > 99 ? '99+' : count).show();
            } else {
                badge.hide();
            }
        }

        // Renderizar notificaciones
        function renderNotifications(notifications) {
            const $list = $('#notifications-list');
            const $empty = $('#notifications-empty');

            if (notifications.length === 0) {
                $list.hide();
                $empty.show();
                return;
            }

            $list.empty().show();
            $empty.hide();

            notifications.forEach(function(notification, index) {
                const isUnread = !notification.is_read;
                const colorClass = getColorClass(notification.color);
                const iconClass = notification.icon || 'fas fa-bell';
                const borderBottom = index < notifications.length - 1 ? 'border-bottom' : '';

                const notificationHtml = `
                    <a href="javascript:void(0)"
                       class="notification-item d-block text-decoration-none ${borderBottom} ${isUnread ? 'bg-light-subtle' : ''}"
                       data-notification-id="${notification.id}"
                       data-action-url="${notification.action_url || '#'}"
                       style="transition: all 0.2s ease;">
                        <div class="d-flex align-items-start gap-3 py-3 px-4">
                            <div class="flex-shrink-0">
                                <span class="bg-${colorClass}-subtle rounded-circle d-flex align-items-center justify-content-center text-${colorClass}"
                                      style="width: 42px; height: 42px; font-size: 16px;">
                                    <i class="${iconClass}"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1" style="min-width: 0;">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                    <h6 class="mb-0 fw-semibold" style="font-size: 13px; line-height: 1.4;">${notification.title}</h6>
                                    ${isUnread ? '<span class="badge bg-primary rounded-pill px-2" style="font-size: 9px; padding: 2px 6px;">NUEVO</span>' : ''}
                                </div>
                                <p class="mb-1 text-muted" style="font-size: 12px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    ${notification.message}
                                </p>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <small class="text-muted" style="font-size: 11px;">
                                        <i class="fas fa-clock me-1" style="font-size: 10px;"></i>${notification.created_at}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </a>
                `;

                $list.append(notificationHtml);
            });

            // Agregar efecto hover
            $('.notification-item').hover(
                function() { $(this).addClass('bg-light'); },
                function() {
                    if (!$(this).hasClass('bg-light-subtle')) {
                        $(this).removeClass('bg-light');
                    }
                }
            );
        }

        // Obtener clase de color Bootstrap
        function getColorClass(color) {
            const colorMap = {
                'primary': 'primary',
                'success': 'success',
                'danger': 'danger',
                'warning': 'warning',
                'info': 'info',
                'secondary': 'secondary'
            };
            return colorMap[color] || 'primary';
        }

        // Click en notificación
        $(document).on('click', '.notification-item', function(e) {
            e.preventDefault();
            const $item = $(this);
            const notificationId = $item.data('notification-id');
            const actionUrl = $item.data('action-url');

            // Marcar como leída
            markAsRead(notificationId, function() {
                // Redirigir si hay URL de acción
                if (actionUrl && actionUrl !== '#') {
                    window.location.href = actionUrl;
                }
            });
        });

        // Marcar notificación como leída
        function markAsRead(notificationId, callback) {
            $.ajax({
                url: `/api/notifications/${notificationId}/read`,
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                xhrFields: {
                    withCredentials: true
                },
                success: function(response) {
                    unreadCount = response.unread_count;
                    updateBadge(unreadCount);

                    // Actualizar contador de header
                    if (unreadCount > 0) {
                        $('#unread-count-text').text(unreadCount + (unreadCount > 1 ? ' nuevas' : ' nueva')).show();
                    } else {
                        $('#unread-count-text').hide();
                    }

                    // Recargar notificaciones para mostrar la siguiente no leída
                    loadNotifications();

                    if (callback) callback();
                },
                error: function(xhr) {
                    console.error('Error marking notification as read:', xhr);
                }
            });
        }

    });
</script>
@endpush
