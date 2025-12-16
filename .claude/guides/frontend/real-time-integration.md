# Real-time Integration with Laravel Echo

**WebSocket integration for real-time features.**

---

## Echo Setup

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: import.meta.env.VITE_APP_ENV === 'production',
    enabledTransports: ['ws', 'wss'],
});

window.Echo.connector.socket.on('connect', () => {
    console.log('Connected');
});

window.Echo.connector.socket.on('disconnect', () => {
    console.log('Disconnected');
});
```

---

## Public Channel Listener

```javascript
class WarehouseListener {
    constructor() {
        this.init();
    }

    init() {
        window.Echo.channel('warehouses')
            .listen('warehouse.created', (e) => this.onCreated(e))
            .listen('warehouse.updated', (e) => this.onUpdated(e))
            .listen('warehouse.deleted', (e) => this.onDeleted(e));
    }

    onCreated(event) {
        console.log('Created:', event.warehouse);
        toastr.info(event.warehouse.name + ' created');
        if (window.dataTable) window.dataTable.reload();
    }

    onUpdated(event) {
        let $row = $(`tr[data-id="${event.warehouse.id}"]`);
        if ($row.length) {
            $row.find('td:nth-child(2)').text(event.warehouse.name);
            $row.fadeOut(100).fadeIn(100);
        }
    }

    onDeleted(event) {
        $(`tr[data-id="${event.warehouse_id}"]`).fadeOut(function() {
            $(this).remove();
        });
    }
}

window.listener = new WarehouseListener();
```

---

## Private Channel

```javascript
window.Echo.private('user.' + userId)
    .notification((notification) => {
        toastr.success(notification.message);
    })
    .listen('notification', (event) => {
        console.log('Event:', event);
    });
```

---

## Presence Channel

```javascript
window.Echo.join('warehouse.' + warehouseId)
    .here((users) => {
        console.log('Online users:', users);
        updateOnlineList(users);
    })
    .joining((user) => {
        console.log('User joined:', user.name);
        toastr.info(user.name + ' joined');
    })
    .leaving((user) => {
        console.log('User left:', user.name);
        toastr.warning(user.name + ' left');
    });
```

---

## Real-time Table Updates

```javascript
class RealTimeTable {
    constructor(tableSelector, channelName) {
        this.$table = $(tableSelector);
        this.channelName = channelName;
        this.init();
    }

    init() {
        this.loadData();
        this.listenToChanges();
    }

    loadData() {
        $.get('/api/warehouses', (response) => {
            this.renderTable(response.data);
        });
    }

    renderTable(items) {
        let html = items.map(item => 
            `<tr data-id="${item.id}">
                <td>${item.name}</td>
                <td>${item.location}</td>
            </tr>`
        ).join('');
        this.$table.find('tbody').html(html);
    }

    listenToChanges() {
        window.Echo.channel(this.channelName)
            .listen('warehouse.created', (e) => this.addRow(e.warehouse))
            .listen('warehouse.updated', (e) => this.updateRow(e.warehouse))
            .listen('warehouse.deleted', (e) => this.removeRow(e.warehouse_id));
    }

    addRow(item) {
        let html = `<tr data-id="${item.id}"><td>${item.name}</td><td>${item.location}</td></tr>`;
        this.$table.find('tbody').prepend(html);
    }

    updateRow(item) {
        let $row = this.$table.find(`tr[data-id="${item.id}"]`);
        $row.find('td:nth-child(1)').text(item.name);
        $row.fadeOut(100).fadeIn(100);
    }

    removeRow(id) {
        this.$table.find(`tr[data-id="${id}"]`).fadeOut(function() {
            $(this).remove();
        });
    }
}

window.realTimeTable = new RealTimeTable('#table', 'warehouses');
```

---

## Connection Management

```javascript
class ConnectionManager {
    constructor() {
        this.isConnected = true;
        this.init();
    }

    init() {
        window.Echo.connector.socket.on('connect', () => {
            this.isConnected = true;
            this.updateStatus(true);
        });

        window.Echo.connector.socket.on('disconnect', () => {
            this.isConnected = false;
            this.updateStatus(false);
        });
    }

    updateStatus(connected) {
        let $status = $('#sync-status');
        if (connected) {
            $status.removeClass('bg-danger').addClass('bg-success');
        } else {
            $status.removeClass('bg-success').addClass('bg-danger');
        }
    }

    isOnline() {
        return this.isConnected;
    }
}

window.connectionManager = new ConnectionManager();
```

---

## Backend Broadcasting

```php
// Event
class WarehouseCreated implements ShouldBroadcast {
    use Dispatchable, SerializesModels;

    public function __construct(public Warehouse $warehouse) {}

    public function broadcastOn(): array {
        return [new Channel('warehouses')];
    }

    public function broadcastAs(): string {
        return 'warehouse.created';
    }
}

// Dispatch
broadcast(new WarehouseCreated($warehouse))->toOthers();
```

---

## Best Practices

1. Check connection before real-time operations
2. Unsubscribe from channels when done
3. Handle reconnection gracefully
4. Include timestamps in broadcasts
5. Validate data on client-side
6. Provide user feedback

---

**Version:** 1.0
