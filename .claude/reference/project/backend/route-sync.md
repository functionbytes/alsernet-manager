# Route Synchronization System - Complete Guide

## Overview

This system automatically synchronizes Laravel routes with a database table, detecting when routes are added, removed, or modified. It supports **3 different approaches** to access control:

1. **Middleware-Based** (Recommended) - Centralized permission checking
2. **Separate Routing** - Profile-specific customization
3. **Permission-Based** - Fine-grained Spatie permissions

---

## Architecture

### Database Models

#### `AppRoute` Model
Stores metadata about all application routes:
- Route name (e.g., `manager.users.edit`)
- Route path (e.g., `/manager/users/{uid}/edit`)
- HTTP method (GET, POST, PUT, DELETE)
- Profile (manager, callcenter, shop, warehouse, etc.)
- Middleware stack
- Controller and action
- Hash for detecting changes

#### `RoutePermission` Model
Links routes to Spatie permissions (many-to-many relationship)

### Services

#### `RouteSyncService`
Main service that:
- Extracts all routes from Laravel router
- Generates hashes for change detection
- Compares with database to find added/removed routes
- Updates the `app_routes` table
- Provides statistics and filtering

### Commands

#### `routes:sync`
Artisan command to manually synchronize routes:
```bash
php artisan routes:sync

# Force synchronization (optional)
php artisan routes:sync --force
```

---

## 3 Approaches to Access Control

### APPROACH 1: Middleware-Based (RECOMMENDED ⭐)

**Location:** Routes use `check.roles.permissions:{profile}` middleware

**Example from managers.php:**
```php
Route::group([
    'prefix' => 'users',
    'name' => 'users.',
    'middleware' => ['check.roles.permissions:manager'],
], function () {
    Route::get('/', [UsersController::class, 'index'])->name('index');
    Route::get('/create', [UsersController::class, 'create'])->name('create');
    Route::post('/store', [UsersController::class, 'store'])->name('store');
    // ... more routes
});
```

**Advantages:**
- ✅ Centralized permission checking via middleware
- ✅ Consistent behavior across all profiles
- ✅ Easy to audit and modify
- ✅ Clear separation of concerns
- ✅ Works with role hierarchies (super-admin → admin → specific roles)

**Disadvantages:**
- Depends on middleware configuration

**How it works:**
1. Request arrives at route
2. `CheckRolesAndPermissions` middleware validates:
   - Does user have a role in the allowed list?
   - Does user have the specific permission for this action?
3. If valid → proceed; if not → abort(403)

---

### APPROACH 2: Separate Routing Per Profile

**Location:** Different route groups in each profile file

**Example from callcenters.php (custom routes):**
```php
Route::group([
    'prefix' => 'users',
    'name' => 'users.',
    'middleware' => [
        'auth',
        function ($request, $next) {
            if (!auth()->user()?->hasAnyRole(['super-admin', 'admin', 'callcenter-manager'])) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        }
    ]
], function () {
    Route::get('/', [UsersController::class, 'index'])->name('index');
    // ... routes specific to callcenter
});
```

**Advantages:**
- ✅ Maximum flexibility per profile
- ✅ Can customize routes for specific profiles
- ✅ Better for complex scenarios

**Disadvantages:**
- Code duplication across profiles
- Harder to maintain consistency

**When to use:**
- When profiles need different user management flows
- When some profiles shouldn't access certain features

---

### APPROACH 3: Permission-Based Access Control

**Location:** Each route has `can:permission` middleware

**Example:**
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/users', [UsersController::class, 'index'])
        ->middleware('can:users.view')
        ->name('users.index');

    Route::get('/users/create', [UsersController::class, 'create'])
        ->middleware('can:users.create')
        ->name('users.create');

    Route::post('/users/store', [UsersController::class, 'store'])
        ->middleware('can:users.create')
        ->name('users.store');

    Route::post('/users/update', [UsersController::class, 'update'])
        ->middleware('can:users.update')
        ->name('users.update');

    Route::get('/users/{uid}/destroy', [UsersController::class, 'destroy'])
        ->middleware('can:users.delete')
        ->name('users.destroy');
});
```

**Advantages:**
- ✅ Fine-grained control per action
- ✅ Can check specific permissions
- ✅ Works well with Spatie system
- ✅ Flexible permission combinations

**Disadvantages:**
- Requires permissions configured in database
- More overhead per request
- Need to manage permission relationships

**Required permissions:**
- `users.view` - List and view users
- `users.create` - Create new users
- `users.update` - Edit users
- `users.delete` - Delete users

---

## Current Implementation

Your routes currently use **APPROACH 1 (Middleware-Based)** which is the recommended approach.

### Route Files Updated:
- ✅ `routes/managers.php` - Manager users
- ✅ `routes/callcenters.php` - Callcenter users
- ✅ `routes/shops.php` - Shop users
- ✅ `routes/warehouses.php` - Warehouse users (also updated middleware)

### Route Pattern:
```
/[profile]/users                    → List users
/[profile]/users/create             → Create form
/[profile]/users/store              → Store (POST)
/[profile]/users/{uid}              → View user
/[profile]/users/{uid}/edit         → Edit form
/[profile]/users/{uid}/update       → Update (POST)
/[profile]/users/{uid}/destroy      → Delete
```

---

## Usage Instructions

### 1. Initial Setup

Run migrations to create the route tables:
```bash
php artisan migrate
```

### 2. Sync Routes

Synchronize all application routes with database:
```bash
php artisan routes:sync
```

Output example:
```
🔄 Starting route synchronization...

📊 Synchronization Results:
   Total routes processed: 234
   ✓ Added routes: 8
      • manager.users.index
      • manager.users.create
      • manager.users.store
      • manager.users.view
      • manager.users.edit
      • manager.users.update
      • manager.users.destroy
      • callcenter.users.index
   ✓ Updated routes: 2
   ✓ Deleted routes: 0

📈 Database Statistics:
   Total routes in database: 234
   Active routes: 234
   Routes by Profile:
      • manager: 45
      • callcenter: 38
      • shop: 32
      • warehouse: 28
      • administrative: 20
   Routes by Method:
      • GET: 156
      • POST: 78
```

### 3. Add New Routes

When you add new routes in any profile file:

**Before:**
```php
// routes/managers.php
Route::group(['prefix' => 'manager'], function () {
    // existing routes...
});
```

**After:**
```php
// routes/managers.php
Route::group(['prefix' => 'manager'], function () {
    // existing routes...

    // NEW ROUTES
    Route::group(['prefix' => 'reports', 'name' => 'reports.'], function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/{id}', [ReportsController::class, 'show'])->name('show');
    });
});
```

**Then sync:**
```bash
php artisan routes:sync
```

The system will:
1. Detect the 2 new routes
2. Generate unique hashes for them
3. Add them to `app_routes` table
4. Log the addition

### 4. Remove Routes

When you remove routes from any profile file:

**Before:**
```php
Route::get('/old-route', [OldController::class, 'action'])->name('old.route');
```

**After:**
```php
// Route removed entirely
```

**Then sync:**
```bash
php artisan routes:sync
```

The system will:
1. Detect the route is no longer in the router
2. Find it in the database by hash
3. Delete it from `app_routes` table
4. Log the deletion

### 5. Modify Routes

When you change a route (name, path, method, or profile):

**Before:**
```php
Route::get('/users', [UsersController::class, 'index'])
    ->name('users.index');
```

**After:**
```php
Route::get('/all-users', [UsersController::class, 'index'])
    ->name('users.list'); // Name changed!
```

**Then sync:**
```bash
php artisan routes:sync
```

The system will:
1. Generate new hash for modified route
2. Mark old route as deleted
3. Add new route as added
4. Log both changes

---

## Helper Class Usage

Use `RouteHelper` to implement different approaches:

```php
// Use APPROACH 1 (Middleware-Based)
\App\Helpers\RouteHelper::registerUserRoutesMiddlewareBased('manager');

// Use APPROACH 2 (Separate Routing)
\App\Helpers\RouteHelper::registerUserRoutesSeparate('callcenter');

// Use APPROACH 3 (Permission-Based)
\App\Helpers\RouteHelper::registerUserRoutesPermissionBased();

// Get approach descriptions
$approaches = \App\Helpers\RouteHelper::getApproachDescriptions();
```

---

## Database Queries

### Get all routes by profile
```php
use App\Models\AppRoute;

$managerRoutes = AppRoute::byProfile('manager')->active()->get();
```

### Get routes by method
```php
$getRoutes = AppRoute::byMethod('GET')->get();
```

### Get statistics
```php
$stats = AppRoute::count(); // Total routes
$active = AppRoute::active()->count(); // Active routes
$byProfile = AppRoute::select('profile')
    ->groupBy('profile')
    ->selectRaw('count(*) as count')
    ->pluck('count', 'profile');
```

### Check if route exists
```php
$exists = AppRoute::where('name', 'manager.users.index')->exists();
```

### Get route with permissions
```php
$route = AppRoute::with('permissions')->find($id);
```

---

## Automatic Sync (Optional)

To automatically sync routes after each deployment or code change, add to your deployment script:

```bash
# In your deployment script or CI/CD pipeline
php artisan routes:sync
```

Or create a listener that syncs on application boot (in development):

```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    if (app()->isLocal()) {
        // Sync routes on every boot in development
        $this->app->make(\App\Services\Systems\RouteSyncService::class)->syncAllRoutes();
    }
}
```

---

## Troubleshooting

### Routes not being detected
- Ensure routes have a name: `->name('route.name')`
- Check if route is being skipped (debugbar, ignition, /api/* routes are skipped)
- Verify the profile is correctly detected

### Routes deleted unexpectedly
- Check if the route file was modified
- Verify the route name/path/method combination is unique
- Check git history if using version control

### Permissions not enforcing
- Ensure `CheckRolesAndPermissions` middleware is registered in `bootstrap/app.php`
- Verify roles and permissions exist in database
- Check user has the correct role assigned

### Conflict with multiple approaches
- Use only ONE approach per profile
- Don't mix middleware-based with permission-based on same routes
- Choose APPROACH 1 for consistency

---

## Best Practices

1. ✅ **Always use names for routes** - Required for detection
2. ✅ **Sync routes after major changes** - Run `routes:sync` after adding/removing routes
3. ✅ **Use APPROACH 1** - Middleware-based is most maintainable
4. ✅ **Keep profiles consistent** - Use same naming conventions
5. ✅ **Document permission requirements** - Add comments for special cases
6. ✅ **Test after sync** - Verify routes still work after synchronization
7. ✅ **Version control routes** - Routes should be in git
8. ✅ **Use logging** - Monitor what changes are detected

---

## Next Steps

1. Run migrations: `php artisan migrate`
2. Sync routes: `php artisan routes:sync`
3. Test the user management routes
4. Configure permissions if using APPROACH 3
5. Monitor logs for any access denial attempts

---

## Support Files

- **Models:** `app/Models/AppRoute.php`, `app/Models/RoutePermission.php`
- **Service:** `app/Services/RouteSyncService.php`
- **Command:** `app/Console/Commands/SyncRoutesCommand.php`
- **Helper:** `app/Helpers/RouteHelper.php`
- **Routes:** `routes/shared/user-routes.php` (documentation only)
- **Migrations:** `database/migrations/2024_11_29_create_app_routes_table.php`, `2024_11_29_create_route_permissions_table.php`
