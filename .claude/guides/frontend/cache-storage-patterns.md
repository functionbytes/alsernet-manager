# Client-side Storage & Caching Patterns with jQuery

---

## Overview

Alsernet uses three types of client-side storage:
- **localStorage** - Persistent (survives page reload)
- **sessionStorage** - Temporary (cleared on tab close)
- **IndexedDB** - Large datasets (async, indexed storage)

---

## PART 1: LOCAL STORAGE

## 1. Basic localStorage Usage

```javascript
// Save data
localStorage.setItem('warehouse-preference', 'warehouse-123');

// Get data
let warehouseId = localStorage.getItem('warehouse-preference');

// Remove data
localStorage.removeItem('warehouse-preference');

// Clear all
localStorage.clear();

// Check if key exists
if (localStorage.getItem('warehouse-preference') !== null) {
    console.log('Preference found');
}
```

---

## 2. Store JSON Objects

```javascript
jQuery(function($) {
    // Save user preferences
    let preferences = {
        theme: 'dark',
        language: 'es',
        defaultWarehouse: 'warehouse-123'
    };

    localStorage.setItem('user-preferences', JSON.stringify(preferences));

    // Retrieve and parse
    let savedPrefs = JSON.parse(localStorage.getItem('user-preferences'));
    console.log(savedPrefs.theme); // 'dark'

    // Update single preference
    savedPrefs.theme = 'light';
    localStorage.setItem('user-preferences', JSON.stringify(savedPrefs));
});
```

---

## 3. Storage Events (Cross-Tab Sync)

```javascript
jQuery(function($) {
    // Listen for storage changes in OTHER tabs
    window.addEventListener('storage', function(e) {
        if (e.key === 'user-preferences') {
            let preferences = JSON.parse(e.newValue);
            console.log('Preferences updated in another tab:', preferences);

            // Update UI
            updateTheme(preferences.theme);
            updateLanguage(preferences.language);
        }
    });

    // Change preference (triggers storage event in other tabs)
    $('#theme-select').on('change', function() {
        let theme = $(this).val();
        let prefs = JSON.parse(localStorage.getItem('user-preferences'));
        prefs.theme = theme;
        localStorage.setItem('user-preferences', JSON.stringify(prefs));
    });
});
```

---

## 4. localStorage with Expiration

```javascript
// resources/js/utils/storage-service.js
class StorageService {
    static setWithExpire(key, value, expiryMinutes) {
        let data = {
            value: value,
            expiry: Date.now() + (expiryMinutes * 60 * 1000)
        };
        localStorage.setItem(key, JSON.stringify(data));
    }

    static getWithExpire(key) {
        let data = JSON.parse(localStorage.getItem(key));

        if (!data) return null;

        if (Date.now() > data.expiry) {
            localStorage.removeItem(key);
            return null;
        }

        return data.value;
    }
}

// Usage
jQuery(function($) {
    // Save with 30 minute expiry
    StorageService.setWithExpire('warehouse-list', warehouseData, 30);

    // Get (null if expired)
    let warehouses = StorageService.getWithExpire('warehouse-list');
});
```

---

## PART 2: SESSION STORAGE

## 5. Basic sessionStorage Usage

```javascript
jQuery(function($) {
    // Store temporary form state
    $('#warehouse-form').on('change input', function() {
        let formData = {
            name: $('#name').val(),
            location: $('#location').val(),
            timestamp: Date.now()
        };
        sessionStorage.setItem('warehouse-form-draft', JSON.stringify(formData));
    });

    // Restore on page load
    let draft = sessionStorage.getItem('warehouse-form-draft');
    if (draft) {
        let data = JSON.parse(draft);
        $('#name').val(data.name);
        $('#location').val(data.location);
        console.log('Form restored from draft');
    }

    // Clear on successful submit
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();
        // ... submit logic
        sessionStorage.removeItem('warehouse-form-draft');
    });
});
```

---

## 6. Tab State Management with sessionStorage

```javascript
jQuery(function($) {
    // Generate unique tab ID
    let tabId = sessionStorage.getItem('tab-id') || 'tab-' + Date.now();
    sessionStorage.setItem('tab-id', tabId);

    // Track last active tab
    $(document).on('click', '.nav-link', function() {
        let tabName = $(this).data('tab');
        sessionStorage.setItem('last-active-tab', tabName);
    });

    // Restore active tab on reload
    let lastTab = sessionStorage.getItem('last-active-tab');
    if (lastTab) {
        $(`.nav-link[data-tab="${lastTab}"]`).tab('show');
    }
});
```

---

## PART 3: INDEXED DB

## 7. IndexedDB Wrapper

```javascript
// resources/js/cache/indexed-db-service.js
class IndexedDBService {
    constructor(dbName = 'Alsernet', version = 1) {
        this.dbName = dbName;
        this.version = version;
        this.db = null;
    }

    // Initialize database
    async init() {
        return new Promise((resolve, reject) => {
            let request = indexedDB.open(this.dbName, this.version);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                let db = event.target.result;

                // Create stores (tables)
                if (!db.objectStoreNames.contains('warehouses')) {
                    db.createObjectStore('warehouses', { keyPath: 'id' });
                }

                if (!db.objectStoreNames.contains('returns')) {
                    db.createObjectStore('returns', { keyPath: 'id' });
                }

                if (!db.objectStoreNames.contains('tickets')) {
                    db.createObjectStore('tickets', { keyPath: 'id' });
                }
            };
        });
    }

    // Save data
    async save(storeName, data) {
        return new Promise((resolve, reject) => {
            let transaction = this.db.transaction([storeName], 'readwrite');
            let store = transaction.objectStore(storeName);
            let request = store.put(data); // put = insert or update

            request.onsuccess = () => resolve(data);
            request.onerror = () => reject(request.error);
        });
    }

    // Save multiple items
    async saveMultiple(storeName, items) {
        return Promise.all(items.map(item => this.save(storeName, item)));
    }

    // Get single item
    async get(storeName, id) {
        return new Promise((resolve, reject) => {
            let transaction = this.db.transaction([storeName], 'readonly');
            let store = transaction.objectStore(storeName);
            let request = store.get(id);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // Get all items
    async getAll(storeName) {
        return new Promise((resolve, reject) => {
            let transaction = this.db.transaction([storeName], 'readonly');
            let store = transaction.objectStore(storeName);
            let request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    // Delete item
    async delete(storeName, id) {
        return new Promise((resolve, reject) => {
            let transaction = this.db.transaction([storeName], 'readwrite');
            let store = transaction.objectStore(storeName);
            let request = store.delete(id);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    // Clear entire store
    async clear(storeName) {
        return new Promise((resolve, reject) => {
            let transaction = this.db.transaction([storeName], 'readwrite');
            let store = transaction.objectStore(storeName);
            let request = store.clear();

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    // Query with filter
    async query(storeName, filterFn) {
        let all = await this.getAll(storeName);
        return all.filter(filterFn);
    }
}

// Initialize on app load
let db = new IndexedDBService('Alsernet', 1);
db.init().then(() => {
    console.log('IndexedDB initialized');
}).catch(error => {
    console.error('IndexedDB init failed:', error);
});
```

---

## 8. Cache Warehouse Data

```javascript
// resources/js/cache/warehouse-cache.js
jQuery(function($) {
    let dbService = window.dbService; // Initialized globally

    function cacheWarehouses() {
        $.get('/api/warehouses', async function(warehouses) {
            // Save to IndexedDB
            await dbService.clear('warehouses');
            await dbService.saveMultiple('warehouses', warehouses);

            // Also save timestamp
            localStorage.setItem('warehouses-cached-at', Date.now());

            console.log('Warehouses cached:', warehouses.length);
        });
    }

    async function getWarehouses() {
        // Try cache first
        let cached = await dbService.getAll('warehouses');

        if (cached.length > 0) {
            console.log('Using cached warehouses');
            return cached;
        }

        // Fetch from server
        return new Promise((resolve) => {
            $.get('/api/warehouses', function(warehouses) {
                resolve(warehouses);
                cacheWarehouses(); // Update cache in background
            });
        });
    }

    // Load and display
    getWarehouses().then(warehouses => {
        renderWarehouses(warehouses);
    });

    // Cache on page load
    cacheWarehouses();
});
```

---

## 9. Cache with Real-time Updates

```javascript
jQuery(function($) {
    let dbService = window.dbService;

    // Initial cache
    function initCache() {
        $.get('/api/warehouses', async function(warehouses) {
            await dbService.saveMultiple('warehouses', warehouses);
            renderWarehouses(warehouses);
        });
    }

    // Listen for real-time updates
    window.Echo.channel('warehouse-updates')
        .listen('warehouse.updated', async (event) => {
            // Update cache
            await dbService.save('warehouses', event.warehouse);

            // Get all warehouses from cache
            let warehouses = await dbService.getAll('warehouses');

            // Update UI
            renderWarehouses(warehouses);

            toastr.info('Cache updated');
        })
        .listen('warehouse.deleted', async (event) => {
            // Remove from cache
            await dbService.delete('warehouses', event.warehouse.id);

            // Get remaining warehouses
            let warehouses = await dbService.getAll('warehouses');

            // Update UI
            renderWarehouses(warehouses);
        });

    initCache();
});
```

---

## 10. Sync Cache with Server

```javascript
// resources/js/cache/sync-service.js
class SyncService {
    constructor(dbService) {
        this.dbService = dbService;
    }

    async syncWarehouses() {
        try {
            // Get cached data
            let cached = await this.dbService.getAll('warehouses');

            // Get server data
            let server = await $.get('/api/warehouses');

            // Find differences
            let newItems = server.filter(s => !cached.find(c => c.id === s.id));
            let updated = server.filter(s => {
                let cached_item = cached.find(c => c.id === s.id);
                return cached_item && JSON.stringify(cached_item) !== JSON.stringify(s);
            });

            // Update cache with new/changed items
            if (newItems.length > 0 || updated.length > 0) {
                await this.dbService.saveMultiple('warehouses', [...newItems, ...updated]);
                console.log('Cache synced:', newItems.length, 'new,', updated.length, 'updated');
            }

            return { newItems, updated };
        } catch (error) {
            console.error('Sync failed:', error);
        }
    }
}

// Usage
jQuery(function($) {
    let syncService = new SyncService(window.dbService);

    // Sync periodically
    setInterval(() => {
        syncService.syncWarehouses();
    }, 5 * 60 * 1000); // Every 5 minutes

    // Also sync on window focus
    window.addEventListener('focus', () => {
        console.log('Page focused, syncing cache');
        syncService.syncWarehouses();
    });
});
```

---

## 11. Offline Fallback

```javascript
jQuery(function($) {
    let isOnline = navigator.onLine;

    // Monitor connection
    window.addEventListener('online', () => {
        console.log('Back online');
        isOnline = true;
        toastr.success('Connection restored');
        syncService.syncWarehouses();
    });

    window.addEventListener('offline', () => {
        console.log('Went offline');
        isOnline = false;
        toastr.warning('No connection');
    });

    // Load warehouses with offline fallback
    function loadWarehouses() {
        if (isOnline) {
            $.get('/api/warehouses', async (warehouses) => {
                await dbService.saveMultiple('warehouses', warehouses);
                renderWarehouses(warehouses);
            }).fail(() => {
                // Fallback to cache
                dbService.getAll('warehouses').then(warehouses => {
                    renderWarehouses(warehouses);
                    toastr.warning('Using cached data');
                });
            });
        } else {
            // Use cache
            dbService.getAll('warehouses').then(warehouses => {
                renderWarehouses(warehouses);
                toastr.info('Offline mode');
            });
        }
    }

    loadWarehouses();
});
```

---

## 12. Storage Size Management

```javascript
// resources/js/utils/storage-monitor.js
class StorageMonitor {
    static async getIndexedDBSize() {
        if ('storage' in navigator && 'estimate' in navigator.storage) {
            let estimate = await navigator.storage.estimate();
            let usage = estimate.usage;
            let quota = estimate.quota;
            let percent = (usage / quota) * 100;

            console.log(`Storage: ${Math.round(usage / 1024 / 1024)}MB / ${Math.round(quota / 1024 / 1024)}MB (${percent.toFixed(2)}%)`);
            return percent;
        }
    }

    static getLocalStorageSize() {
        let size = new Blob(Object.values(localStorage)).size;
        console.log(`LocalStorage: ${Math.round(size / 1024)}KB`);
        return size;
    }

    static async cleanup() {
        // Remove old cached items
        let oldWarehouses = await dbService.query('warehouses', item => {
            return item.created_at < Date.now() - (7 * 24 * 60 * 60 * 1000); // Older than 7 days
        });

        for (let item of oldWarehouses) {
            await dbService.delete('warehouses', item.id);
        }

        console.log('Cleaned up', oldWarehouses.length, 'old items');
    }
}

// Usage
jQuery(function($) {
    StorageMonitor.getIndexedDBSize();
    StorageMonitor.getLocalStorageSize();

    // Cleanup on app init
    StorageMonitor.cleanup();
});
```

---

## Storage Comparison

| Feature | localStorage | sessionStorage | IndexedDB |
|---------|--------------|----------------|-----------|
| **Size** | ~5-10MB | ~5-10MB | ~50MB+ |
| **Persistence** | ✅ Permanent | ❌ Tab-only | ✅ Permanent |
| **Async** | ❌ Sync | ❌ Sync | ✅ Async |
| **Use Case** | Preferences | Form drafts | Large datasets |
| **Speed** | Fast | Fast | Medium |
| **Queryable** | ❌ No | ❌ No | ✅ Yes |

---

## Best Practices

1. **Always check quota** before saving large amounts
2. **Handle storage errors** - not always available
3. **Set expiry on cache** - don't keep stale data
4. **Sync periodically** - keep cache fresh
5. **Clear on logout** - remove user-specific data
6. **Test offline** - ensure fallbacks work
7. **Monitor size** - prevent quota exceeded errors
8. **Use localStorage for preferences** - small, persistent
9. **Use sessionStorage for drafts** - temporary, per tab
10. **Use IndexedDB for large data** - scalable, async

---

## Full Example: Warehouse Cache

```javascript
// Complete warehouse caching system
jQuery(async function($) {
    // 1. Initialize IndexedDB
    let dbService = new IndexedDBService('Alsernet', 1);
    await dbService.init();

    // 2. Load from cache or server
    let warehouses = await getWarehouses();
    renderWarehouses(warehouses);

    // 3. Listen for updates
    window.Echo.channel('warehouse-updates')
        .listen('warehouse.updated', async (event) => {
            await dbService.save('warehouses', event.warehouse);
            warehouses = await dbService.getAll('warehouses');
            renderWarehouses(warehouses);
        });

    async function getWarehouses() {
        let cached = await dbService.getAll('warehouses');
        if (cached.length > 0) return cached;

        let fresh = await $.get('/api/warehouses');
        await dbService.saveMultiple('warehouses', fresh);
        return fresh;
    }
});
```

