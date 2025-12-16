# Real-time Patterns with Laravel Echo + jQuery

## Overview

Real-time features in Alsernet use:
- **Laravel Reverb**: WebSocket server
- **Laravel Broadcasting**: Event channels
- **Laravel Echo**: Client library
- **jQuery**: Event handlers

---

## 1. Bootstrap Echo

### Basic Setup

```javascript
// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    wsHost: import.meta.env.VITE_PUSHER_HOST,
    wsPort: import.meta.env.VITE_PUSHER_PORT,
    wssPort: import.meta.env.VITE_PUSHER_WSS_PORT,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
});
```

### .env Configuration

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST=127.0.0.1
VITE_PUSHER_PORT=8000
VITE_PUSHER_CLUSTER="${PUSHER_APP_CLUSTER}"
```

---

## 2. Public Channel (Everyone)

### Backend: Define Channel

```php
// app/Events/WarehouseUpdated.php
namespace App\Events;

use App\Models\Warehouse;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WarehouseUpdated implements ShouldBroadcast
{
    public function __construct(public Warehouse $warehouse) {}

    public function broadcastOn(): array {
        return [new Channel('warehouse-updates')];
    }

    public function broadcastAs(): string {
        return 'warehouse.updated';
    }

    public function broadcastWith(): array {
        return ['warehouse' => $this->warehouse];
    }
}
```

### Backend: Dispatch Event

```php
// In your controller or job
event(new WarehouseUpdated($warehouse));
```

### Frontend: Listen with jQuery

```javascript
// resources/js/listeners/warehouse-listener.js
jQuery(function($) {
    window.Echo.channel('warehouse-updates')
        .listen('warehouse.updated', (event) => {
            console.log('Warehouse updated:', event.warehouse);

            // Update table row
            let $row = $(`tr[data-id="${event.warehouse.id}"]`);
            if ($row.length) {
                $row.find('td:eq(1)').text(event.warehouse.name);
                $row.find('td:eq(2)').text(event.warehouse.location);
                $row.addClass('highlight-changed');
                setTimeout(() => $row.removeClass('highlight-changed'), 2000);
            }

            // Show notification
            toastr.info(`Warehouse "${event.warehouse.name}" updated`);
        });
});
```

---

## 3. Private Channel (Authenticated Users)

### Backend: Define Channel

```php
// app/Events/UserNotification.php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserNotification implements ShouldBroadcast
{
    public function __construct(
        public $user,
        public $message,
        public $type = 'info'
    ) {}

    public function broadcastOn(): array {
        return [new PrivateChannel('user.' . $this->user->id)];
    }

    public function broadcastAs(): string {
        return 'user.notified';
    }
}
```

### Channel Authorization (routes/channels.php)

```php
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

### Frontend: Listen to Private Channel

```javascript
// resources/js/listeners/notification-listener.js
jQuery(function($) {
    let userId = $('meta[name="user-id"]').attr('content');

    window.Echo.private('user.' + userId)
        .listen('user.notified', (event) => {
            console.log('Notification:', event.message);

            // Show toast based on type
            let method = event.type || 'info';
            toastr[method](event.message);
        });
});
```

---

## 4. Presence Channel (Who's Online)

### Backend: Define Channel

```php
// app/Events/UserPresence.php
namespace App\Events;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserPresence implements ShouldBroadcast
{
    public function broadcastOn(): array {
        return [new PresenceChannel('warehouse-room')];
    }
}
```

### Channel Authorization (routes/channels.php)

```php
Broadcast::channel('warehouse-room', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar_url
    ];
});
```

### Frontend: Track Presence with jQuery

```javascript
// resources/js/listeners/presence-listener.js
jQuery(function($) {
    window.Echo.join('warehouse-room')
        .here((users) => {
            console.log('Users here:', users);
            updateOnlineUsers(users);
        })
        .joining((user) => {
            console.log('User joined:', user.name);
            addOnlineUser(user);
            toastr.info(`${user.name} joined`);
        })
        .leaving((user) => {
            console.log('User left:', user.name);
            removeOnlineUser(user);
            toastr.info(`${user.name} left`);
        })
        .error((error) => {
            console.error('Presence error:', error);
        });
});

function updateOnlineUsers(users) {
    let html = '';
    users.forEach(user => {
        html += `<div class="online-user" data-id="${user.id}">
            <img src="${user.avatar}" class="avatar">
            <span>${user.name}</span>
        </div>`;
    });
    $('#online-users').html(html);
}

function addOnlineUser(user) {
    let html = `<div class="online-user" data-id="${user.id}">
        <img src="${user.avatar}" class="avatar">
        <span>${user.name}</span>
    </div>`;
    $('#online-users').append(html);
}

function removeOnlineUser(user) {
    $(`.online-user[data-id="${user.id}"]`).fadeOut(300, function() {
        $(this).remove();
    });
}
```

---

## 5. Real-time Table Updates

### Backend: Broadcast Event on Update

```php
// app/Models/Warehouse.php
protected static function booted() {
    static::updated(function ($warehouse) {
        event(new WarehouseUpdated($warehouse));
    });

    static::created(function ($warehouse) {
        event(new WarehouseCreated($warehouse));
    });

    static::deleted(function ($warehouse) {
        event(new WarehouseDeleted($warehouse));
    });
}
```

### Frontend: Update DataTable

```javascript
// resources/js/handlers/warehouse-table.js
jQuery(function($) {
    let table = $('#warehouses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/api/warehouses/list'
    });

    // Listen for updates
    window.Echo.channel('warehouse-updates')
        .listen('warehouse.updated', (event) => {
            console.log('Warehouse updated, reloading table');
            table.ajax.reload(null, false); // Keep pagination
        })
        .listen('warehouse.created', (event) => {
            console.log('New warehouse created');
            table.ajax.reload();
            toastr.success('New warehouse added');
        })
        .listen('warehouse.deleted', (event) => {
            console.log('Warehouse deleted');
            table.ajax.reload();
            toastr.info('Warehouse removed');
        });
});
```

---

## 6. Real-time Dashboard

### Backend: Broadcast Dashboard Data

```php
// app/Events/DashboardUpdated.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DashboardUpdated implements ShouldBroadcast
{
    public function __construct(
        public $warehouseCount,
        public $returnCount,
        public $ticketCount,
        public $recentActivity
    ) {}

    public function broadcastOn(): array {
        return [new Channel('dashboard-updates')];
    }

    public function broadcastAs(): string {
        return 'dashboard.updated';
    }

    public function broadcastWith(): array {
        return [
            'warehouse_count' => $this->warehouseCount,
            'return_count' => $this->returnCount,
            'ticket_count' => $this->ticketCount,
            'recent_activity' => $this->recentActivity
        ];
    }
}
```

### Frontend: Live Dashboard

```javascript
// resources/js/listeners/dashboard-listener.js
jQuery(function($) {
    // Load initial metrics
    function loadMetrics() {
        $.get('/api/dashboard/metrics', function(metrics) {
            $('#warehouse-count').text(metrics.warehouse_count);
            $('#return-count').text(metrics.return_count);
            $('#ticket-count').text(metrics.ticket_count);
            updateActivityFeed(metrics.recent_activity);
        });
    }

    // Initial load
    loadMetrics();

    // Listen for updates
    window.Echo.channel('dashboard-updates')
        .listen('dashboard.updated', (event) => {
            // Animate count changes
            animateCountChange('#warehouse-count', event.warehouse_count);
            animateCountChange('#return-count', event.return_count);
            animateCountChange('#ticket-count', event.ticket_count);

            // Add activity
            addActivityItem(event.recent_activity[0]);

            toastr.info('Dashboard updated');
        });

    function animateCountChange(selector, newValue) {
        let $el = $(selector);
        let oldValue = parseInt($el.text());

        if (oldValue !== newValue) {
            $el.fadeOut(100, function() {
                $(this).text(newValue).fadeIn(100);
            });
        }
    }

    function updateActivityFeed(activities) {
        let html = '';
        activities.forEach(activity => {
            html += `<li>${activity.user} ${activity.action}</li>`;
        });
        $('#activity-feed').html(html);
    }

    function addActivityItem(activity) {
        let html = `<li>${activity.user} ${activity.action}</li>`;
        $('#activity-feed').prepend(html);
        // Remove oldest if too many
        if ($('#activity-feed li').length > 10) {
            $('#activity-feed li:last').remove();
        }
    }
});
```

---

## 7. Error Handling & Reconnection

### Monitor Connection Status

```javascript
// resources/js/config/echo-status.js
jQuery(function($) {
    window.Echo.connector.socket.on('connect', () => {
        console.log('Echo connected');
        $('#connection-status').addClass('connected').text('Online');
    });

    window.Echo.connector.socket.on('disconnect', () => {
        console.log('Echo disconnected');
        $('#connection-status').removeClass('connected').text('Offline');
        toastr.warning('Lost connection to server');
    });

    window.Echo.connector.socket.on('connect_error', (error) => {
        console.error('Connection error:', error);
        toastr.error('Connection error');
    });
});
```

### Automatic Reconnection

Echo handles reconnection automatically, but you can monitor it:

```javascript
jQuery(function($) {
    let reconnectAttempts = 0;

    window.Echo.connector.socket.on('connect_error', () => {
        reconnectAttempts++;
        console.log('Reconnect attempt:', reconnectAttempts);

        if (reconnectAttempts > 5) {
            toastr.error('Server connection lost. Please refresh the page.');
        }
    });

    window.Echo.connector.socket.on('connect', () => {
        if (reconnectAttempts > 0) {
            toastr.success('Connection restored');
        }
        reconnectAttempts = 0;
    });
});
```

---

## 8. Unsubscribe from Channels

```javascript
// Leave channel
window.Echo.leave('warehouse-updates');

// Leave all channels
window.Echo.disconnect();

// Usage example
jQuery(function($) {
    // Subscribe when modal opens
    $('#warehouse-modal').on('show.bs.modal', function() {
        window.Echo.channel('warehouse-updates')
            .listen('warehouse.updated', handleUpdate);
    });

    // Unsubscribe when modal closes
    $('#warehouse-modal').on('hide.bs.modal', function() {
        window.Echo.leave('warehouse-updates');
    });
});
```

---

## 9. Testing Real-time Features

### Artisan Tinker for Testing

```bash
# Start tinker
php artisan tinker

# Dispatch event
event(new \App\Events\WarehouseUpdated(\App\Models\Warehouse::first()))

# Create event
\App\Models\Warehouse::create(['name' => 'Test', 'location' => 'Madrid'])
```

### Browser Console Testing

```javascript
// In browser console, after loading page
window.Echo.channel('warehouse-updates')
    .listen('warehouse.updated', (event) => {
        console.log('Received event:', event);
    });

// Manually trigger update from tinker
```

---

## 10. Best Practices

1. **Always initialize Echo in bootstrap.js** - Load it first
2. **Listen to channels on page load** - In DOMReady
3. **Unsubscribe when leaving** - Avoid memory leaks
4. **Handle connection errors** - Show offline indicator
5. **Debounce rapid updates** - Don't spam DOM updates
6. **Use jQuery for DOM changes** - Consistent with codebase
7. **Keep events simple** - Minimal payload
8. **Test with multiple tabs** - Ensure real-time sync
9. **Monitor network usage** - WebSockets are persistent
10. **Log important events** - For debugging

---

## Real-world Example: Warehouse Inventory

### Backend Event

```php
// app/Events/InventoryUpdated.php
class InventoryUpdated implements ShouldBroadcast {
    public function __construct(public Warehouse $warehouse) {}

    public function broadcastOn(): array {
        return [new Channel('inventory.' . $this->warehouse->id)];
    }
}
```

### Frontend Listener

```javascript
jQuery(function($) {
    let warehouseId = $('meta[name="warehouse-id"]').attr('content');

    window.Echo.channel('inventory.' + warehouseId)
        .listen('inventory.updated', (event) => {
            let warehouse = event.warehouse;

            // Update status badge
            $('#inventory-status')
                .text(`${warehouse.current_items}/${warehouse.capacity}`)
                .removeClass('badge-success badge-warning badge-danger');

            // Color based on capacity
            let percent = (warehouse.current_items / warehouse.capacity) * 100;
            if (percent < 50) {
                $('#inventory-status').addClass('badge-success');
            } else if (percent < 80) {
                $('#inventory-status').addClass('badge-warning');
            } else {
                $('#inventory-status').addClass('badge-danger');
            }

            // Update progress bar
            $('#inventory-progress').css('width', percent + '%');

            // Notify
            toastr.info(`Inventory updated: ${warehouse.current_items} items`);
        });
});
```

