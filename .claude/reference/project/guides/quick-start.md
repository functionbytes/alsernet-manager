# Route Synchronization System - Quick Start Guide

**⏱️ Time to get started: 5 minutes**

---

## What You Have

A complete, production-ready route synchronization system that:
- ✅ Automatically syncs routes from files to database
- ✅ Monitors route files for real-time changes
- ✅ Runs as a background daemon via Supervisor
- ✅ Integrates with Spatie roles/permissions
- ✅ Supports development and production

---

## 5-Minute Setup

### 1. Run Migrations (1 minute)

```bash
php artisan migrate
```

**What it does:** Creates `app_routes` and `route_permissions` tables.

---

### 2. Sync Routes (1 minute)

```bash
php artisan routes:sync
```

**What it does:**
- Reads all routes from Laravel router
- Syncs them to database
- Shows summary of added/updated/deleted routes

**Expected output:**
```
✅ Route synchronization completed successfully!
📊 Total routes processed: 45
```

---

### 3. Test Watching (2 minutes)

```bash
# Terminal 1: Start watching
php artisan routes:watch --interval=5

# Terminal 2: Make a change
echo "Route::get('test', fn() => 'test')->name('test.route');" >> routes/managers.php

# Terminal 1: Should detect change
📝 Changes detected at [timestamp]
✓ Modified: routes/managers.php
🔄 Syncing changes...
✅ Sync completed!
```

---

### 4. Clean Up Test (1 minute)

```bash
# Remove test route
# Edit routes/managers.php and delete the test route

# Watch should detect deletion
📝 Changes detected at [timestamp]
✓ Modified: routes/managers.php
🔄 Syncing changes...
✅ Sync completed!
```

---

## Production Setup (Supervisor)

### Option A: Automated Setup (Recommended)

```bash
# For development
sudo ./scripts/setup-supervisor.sh dev

# For production
sudo ./scripts/setup-supervisor.sh prod

# For both
sudo ./scripts/setup-supervisor.sh both
```

**That's it!** The daemon is now running and will:
- Monitor route files continuously
- Sync changes automatically
- Auto-restart if crashes
- Log everything to `storage/logs/supervisor/`

### Option B: Check Status

```bash
sudo supervisorctl status

# Should show:
# laravel-route-watcher-dev    RUNNING   pid XXXX, uptime 0:00:15
# laravel-route-watcher-prod   RUNNING   pid XXXX, uptime 0:00:10
```

---

## Using the System

### Daily Development

```bash
# Option 1: Interactive watching (recommended for active development)
php artisan routes:watch

# Option 2: Just keep supervisor running
sudo supervisorctl status laravel-route-watcher-dev

# Option 3: Manual sync when needed
php artisan routes:sync
```

### Check What's Synced

```bash
php artisan tinker
>>> DB::table('app_routes')->count()
[shows total routes]

>>> AppRoute::byProfile('manager')->count()
[shows routes for 'manager' profile]
```

### View Logs

```bash
# Development
tail -f storage/logs/supervisor/route-watcher-dev.log

# Production
tail -f storage/logs/supervisor/route-watcher-prod.log

# Daemon mode
tail -f storage/logs/route-watcher.log
```

---

## Common Tasks

### Add Permission to New Route

After a new route is synced to the database:

```php
use App\Models\AppRoute;
use Spatie\Permission\Models\Permission;

// Find the route
$route = AppRoute::where('name', 'reports.index')->first();
$permission = Permission::where('name', 'reports.view')->first();

// Link them
$route->permissions()->attach($permission);
```

### Manually Trigger Sync

```bash
php artisan routes:sync
```

### Stop Background Daemon

```bash
# If using daemon mode
php artisan routes:daemon --stop

# If using supervisor
sudo supervisorctl stop laravel-route-watcher-dev
sudo supervisorctl stop laravel-route-watcher-prod
```

### Restart Daemon

```bash
sudo supervisorctl restart laravel-route-watcher-dev
```

---

## Troubleshooting

### Problem: Routes not syncing

**Solution:**
```bash
# 1. Check if service is running
sudo supervisorctl status laravel-route-watcher-dev

# 2. Check logs
tail -f storage/logs/supervisor/route-watcher-dev-error.log

# 3. Manual sync
php artisan routes:sync

# 4. Check database
php artisan tinker
>>> AppRoute::count()
```

### Problem: "Method getPath does not exist"

**Status:** ✅ Already fixed! The system handles all Laravel versions 7+.

### Problem: Permission denied errors

**Solution:**
```bash
# Make sure script is executable
chmod +x scripts/setup-supervisor.sh

# Run with sudo
sudo ./scripts/setup-supervisor.sh dev
```

### Problem: Service keeps crashing

**Check logs:**
```bash
tail -50 storage/logs/supervisor/route-watcher-dev-error.log
```

For detailed troubleshooting, see `SUPERVISOR_OPERATIONS_GUIDE.md`.

---

## File Structure

```
Project Root/
├── app/
│   ├── Services/
│   │   ├── RouteSyncService.php          # Extracts & syncs routes
│   │   └── RouteFileWatcherService.php   # Monitors file changes
│   ├── Console/Commands/
│   │   ├── SyncRoutesCommand.php         # Manual sync
│   │   ├── WatchRoutesCommand.php        # Interactive watch
│   │   └── StartRouteWatcherDaemonCommand.php  # Background daemon
│   ├── Models/
│   │   ├── AppRoute.php                  # Route model
│   │   └── RoutePermission.php           # Permission linking
│   └── Http/Middleware/
│       └── CheckRolesAndPermissions.php  # Access control
├── config/supervisor/
│   ├── laravel-route-watcher-dev.conf
│   └── laravel-route-watcher-prod.conf
├── scripts/
│   └── setup-supervisor.sh               # Automated setup
├── database/migrations/
│   ├── 2024_11_29_create_app_routes_table.php
│   └── 2024_11_29_create_route_permissions_table.php
├── routes/
│   ├── managers.php                      # Manager routes
│   ├── shops.php                         # Shop routes
│   ├── warehouses.php                    # Warehouse routes
│   └── ...
├── storage/
│   ├── logs/
│   │   └── supervisor/                   # Supervisor logs
│   └── app/
│       └── route-monitor-cache.json      # File hash cache
└── Documentation/
    ├── QUICK_START.md                    # This file
    ├── SETUP_AND_VERIFICATION_GUIDE.md   # Detailed setup
    ├── SUPERVISOR_OPERATIONS_GUIDE.md    # Supervisor info
    ├── SYSTEM_ARCHITECTURE.md            # Technical details
    ├── COMPATIBILITY_FIXES.md            # Version fixes
    ├── ROUTE_SYNC_GUIDE.md               # Route sync details
    └── ROUTE_WATCHER_GUIDE.md            # Watcher details
```

---

## Documentation Map

**Pick what you need:**

| Need | Document |
|------|----------|
| Get started NOW | 👈 You are here! |
| Full setup instructions | `SETUP_AND_VERIFICATION_GUIDE.md` |
| Supervisor details | `SUPERVISOR_OPERATIONS_GUIDE.md` |
| How it works internally | `SYSTEM_ARCHITECTURE.md` |
| Route sync details | `ROUTE_SYNC_GUIDE.md` |
| Route watcher details | `ROUTE_WATCHER_GUIDE.md` |
| Version compatibility | `COMPATIBILITY_FIXES.md` |

---

## 🎯 Recommended Path

### For Development

```
1. Run migrations
2. Run php artisan routes:sync
3. Run php artisan routes:watch
4. Test by adding/removing routes
5. (Optional) Set up supervisor with: sudo ./scripts/setup-supervisor.sh dev
```

### For Production

```
1. Run migrations
2. Run php artisan routes:sync
3. Set up supervisor: sudo ./scripts/setup-supervisor.sh prod
4. Verify: sudo supervisorctl status
5. Monitor: tail -f storage/logs/supervisor/route-watcher-prod.log
```

---

## Support & Troubleshooting

### Quick Fixes

**Q: Daemon not detecting changes?**
A: Check interval is not too long. Development should be 3-5 seconds.

**Q: Routes not syncing?**
A: Run `php artisan routes:sync` manually to verify it works.

**Q: Supervisor service won't start?**
A: Run `sudo ./scripts/setup-supervisor.sh dev` to reconfigure.

**Q: High CPU usage?**
A: Increase check interval (15s in production is good).

### Full Troubleshooting

See `SUPERVISOR_OPERATIONS_GUIDE.md` for comprehensive troubleshooting.

---

## Commands Cheat Sheet

```bash
# Setup & Migrations
php artisan migrate

# Manual Sync
php artisan routes:sync

# Interactive Watch (Development)
php artisan routes:watch --interval=5

# Daemon Mode
php artisan routes:daemon                  # Start
php artisan routes:daemon --status         # Check status
php artisan routes:daemon --stop           # Stop

# Supervisor
sudo ./scripts/setup-supervisor.sh dev     # Setup dev
sudo ./scripts/setup-supervisor.sh prod    # Setup prod
sudo supervisorctl status                  # Check status
sudo supervisorctl restart laravel-route-watcher-dev
tail -f storage/logs/supervisor/route-watcher-dev.log

# Database
php artisan tinker
>>> AppRoute::count()
>>> AppRoute::byProfile('manager')->count()
```

---

## What's Next?

1. ✅ Run setup (migrations + sync)
2. ✅ Test watching (add/remove routes)
3. ✅ Deploy with supervisor
4. ✅ Monitor logs
5. 📚 Read detailed docs as needed

**You're all set!** The system is ready to use.

---

## System Status

| Component | Status |
|-----------|--------|
| Migrations | ✅ Ready to run |
| Route Sync | ✅ Tested & working |
| File Watcher | ✅ Tested & working |
| Supervisor Setup | ✅ Automated |
| Laravel 7+ Compatibility | ✅ Fixed |
| Production Ready | ✅ Yes |

**The system is complete and production-ready!**
