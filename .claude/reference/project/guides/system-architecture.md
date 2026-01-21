# Route Synchronization System - Architecture & Design

## System Overview

The Route Synchronization System is a comprehensive solution for automatically managing and synchronizing Laravel routes from source files to a database. It provides real-time monitoring, multiple access control approaches, and production-ready deployment with Supervisor.

**Core Purpose:** Detect route file changes → Sync routes to database → Enforce permissions through role-based access control

---

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Route Source Files                        │
│  ┌──────────────┬──────────────┬──────────────────────────┐  │
│  │ routes/      │ routes/      │ routes/                  │  │
│  │ managers.php │ shops.php    │ warehouses.php ...       │  │
│  └──────────────┴──────────────┴──────────────────────────┘  │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
        ┌─────────────────────────────────────┐
        │  RouteFileWatcherService            │
        │  (Monitors file changes)            │
        │  - Tracks file hashes              │
        │  - Detects add/modify/delete       │
        │  - Triggers sync on change         │
        └──────────────┬──────────────────────┘
                       │
        ┌──────────────▼──────────────────────┐
        │  RouteSyncService                   │
        │  (Extracts & syncs routes)          │
        │  - Reads Laravel router             │
        │  - Generates route hashes           │
        │  - Compares with database           │
        │  - Creates/updates DB records       │
        └──────────────┬──────────────────────┘
                       │
                       ▼
        ┌─────────────────────────────────────┐
        │  Database Tables                    │
        │  ┌───────────────────────────────┐  │
        │  │ app_routes                    │  │
        │  │ (stores route metadata)       │  │
        │  └───────────────────────────────┘  │
        │  ┌───────────────────────────────┐  │
        │  │ route_permissions             │  │
        │  │ (many-to-many relationships)  │  │
        │  └───────────────────────────────┘  │
        └──────────────┬──────────────────────┘
                       │
                       ▼
        ┌─────────────────────────────────────┐
        │  Middleware / Route Handlers        │
        │  CheckRolesAndPermissions           │
        │  (Enforces access control)          │
        └─────────────────────────────────────┘
```

---

## Component Architecture

### 1. Route Source Files
**Files:** `routes/managers.php`, `routes/shops.php`, `routes/warehouses.php`, etc.

**Responsibility:** Define all application routes with HTTP methods and controllers.

**Design Pattern:** Grouped by user role/profile.

```php
// Example: routes/theme.php
Route::middleware(['auth', 'check.roles.permissions:manager'])
    ->group(function () {
        Route::resource('users', UserController::class);
    });
```

---

### 2. RouteFileWatcherService
**File:** `app/Services/RouteFileWatcherService.php`

**Responsibility:** Monitor route files continuously for changes.

**Key Features:**
- ✅ File hashing (content + modification time)
- ✅ Change detection (add/modify/delete)
- ✅ Cache management
- ✅ Continuous monitoring loop

**How It Works:**

```
1. Start monitoring
   ├─ Load cached file hashes from storage
   └─ If no cache, create initial baseline

2. Every N seconds (interval):
   ├─ Calculate current file hashes
   ├─ Compare with cached hashes
   └─ If changes detected:
       ├─ Identify which files changed
       ├─ Trigger RouteSyncService
       ├─ Save new hashes to cache
       └─ Log changes

3. Repeat loop
```

**Storage Format:**
```json
{
  "routes/managers.php": "abc123def456...",
  "routes/shops.php": "xyz789uvw123...",
  "routes/warehouses.php": "pqr456stu789..."
}
```

**Design Decisions:**
- Uses MD5 hashing for performance (not security)
- Combines file content + mtime for change detection
- Caches hashes to avoid re-hashing large files
- Supports custom monitoring intervals

---

### 3. RouteSyncService
**File:** `app/Services/RouteSyncService.php`

**Responsibility:** Extract routes from Laravel router and synchronize with database.

**Key Features:**
- ✅ Version compatibility (Laravel 7+)
- ✅ Route filtering (skip debug, API, unnamed routes)
- ✅ Profile detection (manager, shop, warehouse, etc.)
- ✅ Hash-based deduplication
- ✅ Atomic sync operations

**How It Works:**

```
1. Extract Laravel Routes
   ├─ Get all routes from Route::getRoutes()
   ├─ Filter out unwanted routes
   ├─ Extract metadata (path, method, controller, etc.)
   └─ Generate unique hash for each route

2. Compare with Database
   ├─ Generate hashes for all extracted routes
   ├─ Query existing routes from database
   ├─ Determine: added, updated, deleted

3. Sync Changes
   ├─ New routes: INSERT into app_routes
   ├─ Changed routes: UPDATE in app_routes
   └─ Removed routes: DELETE from app_routes

4. Return Results
   └─ Summary of changes (added, updated, deleted count)
```

**Hash Generation:**
```php
MD5(route_name + path + method + profile)
```

This ensures:
- Same route always produces same hash
- Detects any changes in route definition
- Enables deduplication

**Compatibility Layer:**

```php
// Laravel has different ways to get path across versions
protected function getRoutePath($route): string
{
    if (method_exists($route, 'getPath')) {      // Laravel 11+
        return $route->getPath();
    } elseif (method_exists($route, 'getUri')) { // Laravel 8-10
        return $route->getUri();
    } elseif (isset($route->uri)) {              // Laravel 7
        return $route->uri;
    } else {                                       // Fallback
        return $route->compiledRoute->getPath() ?? '/';
    }
}
```

**Design Decisions:**
- Uses route names for identification
- Skips unnamed routes (unlikely to have permissions)
- Detects profile from route path prefix
- Allows graceful fallback for missing methods

---

### 4. Database Schema

#### app_routes Table

**Purpose:** Store all application routes with metadata.

**Columns:**
```sql
- id (PK)
- name (VARCHAR) - route name (e.g., 'users.index')
- path (VARCHAR) - route path (e.g., '/users')
- method (VARCHAR) - HTTP methods (e.g., 'GET|POST')
- profile (VARCHAR, nullable) - role/profile (e.g., 'manager')
- middleware (JSON, nullable) - middleware list
- controller (VARCHAR, nullable) - controller class
- action (VARCHAR, nullable) - method name
- requires_auth (BOOLEAN) - if auth required
- is_active (BOOLEAN) - soft delete replacement
- hash (VARCHAR, UNIQUE) - unique route identifier
- description (TEXT, nullable) - human-readable description
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

**Indexes:**
```sql
- PRIMARY KEY (id)
- UNIQUE INDEX (hash)
- INDEX (profile) - frequently filtered
- INDEX (name)
- INDEX (is_active)
- INDEX (method)
```

**Sample Data:**
```sql
INSERT INTO app_routes VALUES (
    1,
    'users.index',
    '/manager/users',
    'GET',
    'manager',
    '["auth","check.roles.permissions:manager"]',
    'App\\Http\\Controllers\\Managers\\Users\\UsersController',
    'index',
    true,
    true,
    'abc123...',
    'List all users in manager profile',
    ...
);
```

#### route_permissions Table

**Purpose:** Many-to-many junction table linking routes to permissions.

**Columns:**
```sql
- id (PK)
- route_id (FK) - references app_routes(id)
- permission_id (FK) - references permissions(id)
- created_at (TIMESTAMP)
```

**Constraints:**
```sql
- UNIQUE (route_id, permission_id) - no duplicates
- FOREIGN KEY (route_id) REFERENCES app_routes(id) ON DELETE CASCADE
- FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
```

**Sample Data:**
```sql
INSERT INTO route_permissions (route_id, permission_id) VALUES
(1, 15),  -- users.index route requires 'view users' permission
(1, 20);  -- users.index route also requires 'manage profile' permission
```

---

### 5. AppRoute Model
**File:** `app/Models/AppRoute.php`

**Responsibility:** Eloquent model for database interactions.

**Key Methods:**
```php
// Generate unique hash
public static function generateHash($name, $path, $method, $profile = null)

// Query scopes
public function scopeByProfile($query, $profile)
public function scopeActive($query)

// Get all available profiles
public static function getProfiles()
```

**Usage:**
```php
// Find by profile
$routes = AppRoute::byProfile('manager')->active()->get();

// Check if route exists
$exists = AppRoute::where('name', 'users.index')->exists();

// Get statistics
$stats = AppRoute::groupBy('profile')->count();
```

---

### 6. Console Commands

#### SyncRoutesCommand
**Command:** `php artisan routes:sync`

**Responsibility:** Manual one-time synchronization of all routes.

**Flow:**
1. Calls `RouteSyncService::syncAllRoutes()`
2. Displays formatted results
3. Shows statistics by profile and method

**When to Use:**
- Initial setup
- After major route file changes
- Troubleshooting/verification

**Output:**
```
🔄 Starting route synchronization...

📊 Synchronization Results:
   Total routes processed: 45
   ✓ Added routes: 5
   ✓ Updated routes: 2
   ✓ Deleted routes: 0

📈 Routes by Profile:
   ├─ manager: 15
   ├─ shop: 18
   └─ warehouse: 12

🔧 Routes by Method:
   ├─ GET: 30
   ├─ POST: 10
   ├─ PUT: 3
   ├─ DELETE: 2
   └─ PATCH: 0
```

#### WatchRoutesCommand
**Command:** `php artisan routes:watch [--interval=N]`

**Responsibility:** Interactive real-time monitoring with terminal output.

**Features:**
- Live display of monitored files
- Real-time change detection
- Automatic sync on changes
- Formatted output
- Graceful exit with Ctrl+C

**When to Use:**
- Development environment
- Testing route changes
- Debugging sync issues
- Manual monitoring sessions

**Output:**
```
🔍 Route File Watcher - Interactive Mode
📁 Monitoring files: 6 files
   ├─ routes/managers.php
   ├─ routes/shops.php
   └─ ...
⏱️ Check interval: 5 seconds
🛑 Press Ctrl+C to stop

Waiting for changes...
[Changes detected - shows live updates]
```

#### StartRouteWatcherDaemonCommand
**Command:** `php artisan routes:daemon [--interval=N] [--status] [--stop]`

**Responsibility:** Background daemon mode for production/continuous operation.

**Features:**
- Background process with PID
- Persistent logging
- Status checking
- Graceful shutdown
- Cross-platform support (Windows, Linux, macOS)

**When to Use:**
- Production environments
- Continuous operation
- Supervisor integration
- Automated deployments

**Options:**
```bash
php artisan routes:daemon                    # Start daemon
php artisan routes:daemon --interval=15      # Custom interval
php artisan routes:daemon --status           # Check if running
php artisan routes:daemon --stop             # Stop daemon gracefully
```

**PID Management:**
```
On start: PID written to storage/app/route-watcher.pid
On stop: PID file deleted
On crash: PID cleaned up gracefully
```

---

### 7. Middleware Integration

#### CheckRolesAndPermissions
**File:** `app/Http/Middleware/CheckRolesAndPermissions.php`

**Responsibility:** Enforce role-based and permission-based access control.

**How It Works:**

```
1. Extract role from URL parameter
   ├─ Example: /manager/users → role = 'manager'
   └─ Defined in roleMapping array

2. Verify user has the role
   ├─ Check Spatie roles
   └─ Super-admin always has access

3. Check permission requirements
   ├─ Map controller action to permission
   ├─ Query database for required permissions
   └─ Verify user has all required permissions

4. If authorized: Continue to controller
   If denied: Log and return 403 Forbidden
```

**Example:**

```php
// Route: /manager/users (UserController@index)
// Middleware extracts: role = 'manager', action = 'index'
// Maps to permission: 'users.index' (from actionToPermission array)
// Checks: Does user have 'manager' role AND 'users.index' permission?

// Access control logic:
CheckRolesAndPermissions::handle() {
    1. $role = 'manager' (from URL)
    2. $permission = 'users.index' (from action)
    3. $user->hasRole('manager') ? continue : deny
    4. $user->hasPermissionTo('users.index') ? continue : deny
}
```

**Audit Logging:**

Every access denial is logged with:
```php
[
    'user_id' => $user->id,
    'user_email' => $user->email,
    'route_name' => $request->route()->getName(),
    'method' => $request->getMethod(),
    'path' => $request->path(),
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'reason' => 'Missing permission: users.create',
    'timestamp' => now(),
]
```

---

### 8. Three Access Control Approaches

The system supports three different approaches to route access control:

#### Approach 1: Middleware-Based (RECOMMENDED)
**File:** `routes/managers.php` (current implementation)

**Concept:** All routes protected by single middleware.

```php
Route::middleware(['auth', 'check.roles.permissions:manager'])
    ->group(function () {
        Route::resource('users', UserController::class);
    });
```

**Pros:**
- ✅ Centralized permission checking
- ✅ Single middleware applies to all
- ✅ Easy to audit and modify
- ✅ DRY principle
- ✅ Works with Spatie permissions

**Cons:**
- ✗ All routes in group must have same profile

---

#### Approach 2: Separate Routing
**Concept:** Different route files per role.

```php
// routes/manager-users.php
Route::middleware(['auth', 'check.role:manager'])
    ->group(function () {
        Route::resource('users', UserController::class);
    });

// routes/admin-users.php
Route::middleware(['auth', 'check.role:admin'])
    ->group(function () {
        Route::resource('users', AdminUserController::class);
    });
```

**Pros:**
- ✅ Clear separation by role
- ✅ Different controllers per role
- ✅ Easy to restrict/allow per role

**Cons:**
- ✗ Code duplication
- ✗ Harder to maintain consistency

---

#### Approach 3: Permission-Based
**Concept:** Every route linked to one or more permissions.

```php
Route::resource('users', UserController::class)
    ->middleware(['auth', 'check.permissions:users.view,users.create']);
```

Uses `route_permissions` table to define which permissions protect each route.

**Pros:**
- ✅ Fine-grained control
- ✅ Per-action permissions
- ✅ Flexible combinations

**Cons:**
- ✗ Complex permission mapping
- ✗ Requires middleware for each route

---

## Deployment Models

### Model 1: Development (Interactive)

```
Developer makes code change
        ↓
Routes file saved
        ↓
WatchRoutesCommand detects change (3-5 second check)
        ↓
RouteSyncService syncs to database
        ↓
Developer sees live feedback in terminal
```

**Commands:**
```bash
php artisan routes:watch                # Interactive watch
php artisan routes:daemon --interval=3  # Daemon mode
```

---

### Model 2: Production (Supervisor)

```
Supervisor starts daemon at boot
        ↓
Routes:daemon runs continuously (15 second checks)
        ↓
File changes detected
        ↓
Database synced automatically
        ↓
Logs written to storage/logs/supervisor/
        ↓
Auto-restarts if crashes
```

**Setup:**
```bash
sudo ./scripts/setup-supervisor.sh prod
```

**Configuration:**
```ini
[program:laravel-route-watcher-prod]
command=php /path/to/artisan routes:daemon --interval=15
autostart=true
autorestart=true
user=www-data
```

---

## Data Flow Example

**Scenario:** Developer adds new route to `routes/managers.php`

### Step 1: File Changes
```php
// routes/theme.php
Route::resource('reports', ReportController::class); // NEW
```

### Step 2: Detection (RouteFileWatcherService)
```
Old hash: 'abc123...'
New hash: 'def456...'
→ Hashes don't match!
→ Change detected
```

### Step 3: Extraction (RouteSyncService)
```
Laravel router contains:
- reports.index: /manager/reports [GET]
- reports.create: /manager/reports/create [GET]
- reports.store: /manager/reports [POST]
- reports.show: /manager/reports/{report} [GET]
- reports.edit: /manager/reports/{report}/edit [GET]
- reports.update: /manager/reports/{report} [PUT]
- reports.destroy: /manager/reports/{report} [DELETE]

Generates hashes for each route
```

### Step 4: Comparison (RouteSyncService)
```
Query database:
- reports.index exists? NO → Mark as NEW
- reports.create exists? NO → Mark as NEW
- reports.store exists? NO → Mark as NEW
...
(All 7 routes are new)
```

### Step 5: Sync (RouteSyncService)
```
INSERT into app_routes:
- id: 46, name: 'reports.index', path: '/manager/reports', ...
- id: 47, name: 'reports.create', path: '/manager/reports/create', ...
...
(7 new routes added)
```

### Step 6: Result
```
Database now contains all 7 report routes
✓ Frontend can link to new routes
✓ Middleware can check permissions
✓ Admin can assign permissions to routes
```

---

## Performance Characteristics

### RouteSyncService
- **Time:** ~100-500ms per sync (depends on route count)
- **Memory:** ~2-5MB per sync
- **Database:** 1 query per route + batch queries
- **I/O:** File reads, database writes

### RouteFileWatcherService
- **Interval 3s:** ~3% CPU, ~1MB memory (development)
- **Interval 15s:** ~0.5% CPU, ~1MB memory (production)
- **Hash calculation:** ~1ms per file
- **Storage:** ~10KB per 100 routes

### Middleware (CheckRolesAndPermissions)
- **Time:** ~5-10ms per request
- **Memory:** ~100KB per request
- **Database:** 1 query for role check, N queries for permissions
- **Caching:** Can be optimized with query caching

---

## Error Handling & Recovery

### Level 1: Service Recovery
- Routes:daemon crashes → Supervisor auto-restarts
- File hash corruption → Recalculated on next check
- Database sync fails → Logged, retried on next interval

### Level 2: Logging
```
storage/logs/supervisor/route-watcher-prod.log
storage/logs/supervisor/route-watcher-prod-error.log
storage/logs/route-watcher.log (daemon mode)
```

### Level 3: Manual Recovery
```bash
# Clear cache and resync
rm storage/app/route-monitor-cache.json
php artisan routes:sync

# Restart supervisor service
sudo supervisorctl restart laravel-route-watcher-prod

# Check logs
tail -f storage/logs/supervisor/route-watcher-prod.log
```

---

## Security Considerations

1. **Route Synchronization**
   - ✅ Only syncs routes from application files (no arbitrary input)
   - ✅ Uses Laravel router (trusted source)
   - ✅ No remote execution or code evaluation

2. **Permission Checking**
   - ✅ Uses Spatie (battle-tested library)
   - ✅ Middleware protection on all routes
   - ✅ Access denials are logged and auditable

3. **File Monitoring**
   - ✅ Only monitors application route files
   - ✅ No file modification by watcher
   - ✅ Runs with application user privileges

4. **Database**
   - ✅ Uses Eloquent (prevents SQL injection)
   - ✅ Hash-based deduplication (not ID-based)
   - ✅ Foreign key constraints prevent orphaned data

---

## Monitoring & Observability

### Key Metrics
- Total routes in database
- Routes by profile
- Routes by method
- Routes requiring authentication
- Permission coverage
- File change frequency
- Sync duration
- Daemon uptime

### Useful Queries
```php
// Get all routes
AppRoute::active()->get();

// Routes by profile
AppRoute::byProfile('manager')->count();

// Recently added routes
AppRoute::where('created_at', '>', now()->subHour())->get();

// Routes without permissions assigned
AppRoute::doesntHave('permissions')->get();

// Routes requiring auth
AppRoute::where('requires_auth', true)->count();
```

---

## Integration Points

### With Spatie Laravel Permission
- Users have roles (belongs to many roles)
- Roles have permissions (belongs to many permissions)
- Middleware checks roles and permissions
- Routes linked to permissions via many-to-many

### With Laravel Authentication
- `Auth::user()` checked for roles/permissions
- Protected by auth middleware
- User context available in CheckRolesAndPermissions

### With Laravel Routing
- Reads from `Route::getRoutes()`
- Syncs route metadata to database
- Supports all route types and methods

---

## Future Enhancement Possibilities

1. **Route Groups**
   - Organize routes into logical groups
   - Assign permissions at group level

2. **API Documentation**
   - Generate OpenAPI/Swagger from synced routes
   - Keep docs in sync with code

3. **Route Audit Trail**
   - Log all route changes
   - Who modified what and when

4. **Cache Layer**
   - Cache permission checks
   - Reduce database queries

5. **Advanced Scheduling**
   - Run sync at specific times
   - Batch route changes

6. **Webhook Notifications**
   - Notify when routes change
   - Integration with monitoring systems
