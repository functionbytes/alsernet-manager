/**
 * Notification Module
 * Manages notification bell dropdown and unread notification handling
 * Routes are passed via data attributes on the notifications component
 */

(function() {
    'use strict';

    // Configuration object - initialized from data attributes
    let config = {
        apiIndexRoute: null,
        apiReadRoute: null,
        markAllReadRoute: null,
        refreshInterval: 60000, // 60 seconds
        limit: 4,
    };

    // State management
    let state = {
        unreadCount: 0,
        refreshTimer: null,
    };

    /**
     * Initialize the notification system
     * Call this after the DOM is ready
     */
    window.NotificationManager = {
        init: function(options = {}) {
            // Merge options with defaults
            Object.assign(config, options);

            // Validate that routes are provided
            if (!config.apiIndexRoute || !config.apiReadRoute) {
                console.warn('NotificationManager: Routes not properly configured');
                return;
            }

            // Load initial notifications
            loadNotifications();

            // Set up auto-refresh
            state.refreshTimer = setInterval(loadNotifications, config.refreshInterval);

            // Set up event listeners
            setupEventListeners();
        },

        /**
         * Manually refresh notifications
         */
        refresh: function() {
            loadNotifications();
        },

        /**
         * Stop the notification system
         */
        destroy: function() {
            if (state.refreshTimer) {
                clearInterval(state.refreshTimer);
                state.refreshTimer = null;
            }
        },

        /**
         * Get current unread count
         */
        getUnreadCount: function() {
            return state.unreadCount;
        },
    };

    /**
     * Load notifications from API
     */
    function loadNotifications() {
        // Build URL with query parameters
        const url = new URL(config.apiIndexRoute, window.location.origin);
        url.searchParams.append('limit', config.limit);
        url.searchParams.append('unread', 'true');

        $.ajax({
            url: url.toString(),
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            xhrFields: {
                withCredentials: true,
            },
            success: function(response) {
                state.unreadCount = response.unread_count;
                updateBadge(state.unreadCount);
                renderNotifications(response.notifications);

                // Hide loading state
                $('#notifications-loading').hide();

                // Update unread count text
                updateUnreadCountText(state.unreadCount);
                updateRemainingText(state.unreadCount);
            },
            error: function(xhr) {
                console.error('Error loading notifications:', xhr);
                handleLoadError();
            },
        });
    }

    /**
     * Mark a notification as read
     */
    function markAsRead(notificationId, callback) {
        // Replace {id} placeholder with actual ID
        const url = config.apiReadRoute.replace('{id}', notificationId);

        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            xhrFields: {
                withCredentials: true,
            },
            success: function(response) {
                state.unreadCount = response.unread_count;
                updateBadge(state.unreadCount);
                updateUnreadCountText(state.unreadCount);

                // Reload notifications to show the next unread one
                loadNotifications();

                if (callback) callback();
            },
            error: function(xhr) {
                console.error('Error marking notification as read:', xhr);
                if (xhr.status === 401) {
                    console.warn('User not authenticated');
                }
            },
        });
    }

    /**
     * Update the notification badge (visual indicator only, no number)
     */
    function updateBadge(count) {
        const $badge = $('#notification-badge');
        if (count > 0) {
            $badge.show();
        } else {
            $badge.hide();
        }
    }

    /**
     * Update the unread count text in the header
     */
    function updateUnreadCountText(count) {
        const $text = $('#unread-count-text');
        if (count > 0) {
            const label = count > 1 ? ' nuevas' : ' nueva';
            $text.text(count + label).show();
        } else {
            $text.hide();
        }
    }

    /**
     * Update the "additional notifications" text
     */
    function updateRemainingText(count) {
        const $totalText = $('#total-notifications-text');
        if (count > config.limit) {
            const remaining = count - config.limit;
            const plural = remaining > 1;
            const itemLabel = plural ? 'notificaciones' : 'notificación';
            const label = plural ? 'adicionales' : 'adicional';
            $totalText.text(`+${remaining} ${itemLabel} ${label}`).show();
        } else if (count > 0) {
            $totalText.text('Ver historial completo').show();
        } else {
            $totalText.hide();
        }
    }

    /**
     * Render notifications in the dropdown
     */
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
            const unreadBadge = isUnread ? '<span class="badge bg-primary px-2" style="font-size: 9px; padding: 2px 6px;">NUEVO</span>' : '';

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
                                ${unreadBadge}
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

        // Add hover effects
        setupNotificationItemHovers();
    }

    /**
     * Set up hover effects for notification items
     */
    function setupNotificationItemHovers() {
        $('.notification-item').off('mouseenter mouseleave').on('mouseenter', function() {
            $(this).addClass('bg-light');
        }).on('mouseleave', function() {
            if (!$(this).hasClass('bg-light-subtle')) {
                $(this).removeClass('bg-light');
            }
        });
    }

    /**
     * Get Bootstrap color class from notification color value
     */
    function getColorClass(color) {
        const colorMap = {
            'primary': 'primary',
            'success': 'success',
            'danger': 'danger',
            'warning': 'warning',
            'info': 'info',
            'secondary': 'secondary',
        };
        return colorMap[color] || 'primary';
    }

    /**
     * Handle load error state
     */
    function handleLoadError() {
        $('#notifications-loading').html(
            '<div class="text-center py-5">' +
            '<i class="fas fa-exclamation-triangle text-warning fs-1 mb-3"></i>' +
            '<p class="text-muted mb-0 small">Error al cargar notificaciones</p>' +
            '</div>'
        );
    }

    /**
     * Set up event listeners for notification interactions
     */
    function setupEventListeners() {
        // Click handler for notification items
        $(document).off('click', '.notification-item').on('click', '.notification-item', function(e) {
            e.preventDefault();
            const $item = $(this);
            const notificationId = $item.data('notification-id');
            const actionUrl = $item.data('action-url');

            markAsRead(notificationId, function() {
                // Redirect if action URL is available
                if (actionUrl && actionUrl !== '#') {
                    window.location.href = actionUrl;
                }
            });
        });
    }

    /**
     * Auto-initialize when document is ready
     * This will be called from the Blade template with proper route parameters
     */
    $(document).ready(function() {
        // Get configuration from data attributes on the notifications component
        const $notificationsComponent = $('#notifications-dropdown');
        if ($notificationsComponent.length) {
            const options = {
                apiIndexRoute: $notificationsComponent.data('api-index-route'),
                apiReadRoute: $notificationsComponent.data('api-read-route'),
                markAllReadRoute: $notificationsComponent.data('mark-all-read-route'),
                refreshInterval: $notificationsComponent.data('refresh-interval') || 60000,
                limit: $notificationsComponent.data('limit') || 4,
            };

            window.NotificationManager.init(options);

            // Listen for real-time notifications via Laravel Echo/Reverb
            if (window.Echo) {
                const userId = $('meta[name="user-id"]').attr('content');
                if (userId) {
                    console.log('📧 Listening for real-time notifications on private channel');
                    window.Echo.private(`user.${userId}`)
                        .notification((notification) => {
                            console.log('🔔 Real-time notification received:', notification);
                            // Refresh notifications dropdown
                            window.NotificationManager.refresh();
                        })
                        .error((error) => {
                            console.warn('⚠️  Notification channel error:', error);
                        });
                }
            } else {
                console.warn('⚠️  Laravel Echo not initialized');
            }
        }
    });
})();
