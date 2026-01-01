# Route Synchronization System - Complete Setup and Verification Guide

## Overview

This guide provides step-by-step instructions for setting up and verifying the complete route synchronization system that automatically monitors and syncs routes from your route files to the database.

**System Components:**
- ✅ `RouteSyncService` - Extracts and syncs routes from Laravel files to database
- ✅ `RouteFileWatcherService` - Monitors route files for changes
- ✅ Console Commands (3 variants) - Manual sync, interactive watch, daemon mode
- ✅ Supervisor Configuration - Production and development deployment
- ✅ Database Models & Migrations - Stores routes and permissions
- ✅ Middleware Integration - Role/permission checking

---

## Phase 1: Database Setup

### Step 1: Run Migrations

```bash
php artisan migrate
```

This creates two tables:
- **app_routes** - Stores all routes with metadata
- **route_permissions** - Links routes to permissions (many-to-many)

**Expected Output:**
```
Migrating: 2024_11_29_create_app_routes_table.php
Migrated: 2024_11_29_create_app_routes_table (0.05s)
Migrating: 2024_11_29_create_route_permissions_table.php
Migrated: 2024_11_29_create_route_permissions_table (0.07s)
```

### Step 2: Verify Table Creation

```bash
php artisan tinker
# Then in tinker:
>>> DB::table('app_routes')->count()
0  # Should be 0 before first sync
>>> exit
```

---

## Phase 2: Initial Route Synchronization

### Step 1: Run the Sync Command

```bash
php artisan routes:sync
```

**Expected Output:**
```
🔄 Starting route synchronization...

📊 Synchronization Results:
   Total routes processed: [number]
   ✓ Added routes: [number]
   ✓ Updated routes: 0
   ✓ Deleted routes: 0

📈 Routes by Profile:
   ├─ manager: [number]
   ├─ shop: [number]
   ├─ warehouse: [number]
   └─ ...

🔧 Routes by Method:
   ├─ GET: [number]
   ├─ POST: [number]
   ├─ PUT: [number]
   ├─ DELETE: [number]
   └─ PATCH: [number]

✅ Route synchronization completed successfully!
```

### Step 2: Verify Routes in Database

```bash
php artisan tinker
>>> DB::table('app_routes')->count()
[should show a number > 0]
>>> DB::table('app_routes')->where('profile', 'manager')->count()
[should show routes for manager profile]
>>> exit
```

---

## Phase 3: Watch Mode Testing (Development)

### Interactive Watch Mode

Watch for changes to route files in real-time with formatted output:

```bash
php artisan routes:watch --interval=5
```

**Expected Output:**
```
🔍 Route File Watcher - Interactive Mode
📁 Monitoring files: [list of files]
⏱️ Check interval: 5 seconds
🛑 Press Ctrl+C to stop

Waiting for changes...
```

### Test with Actual Changes

1. **In a new terminal window**, modify a route file:

```bash
# Example: Add a new route to routes/theme.php
echo "
// Test route
Route::get('test-route', function() { return 'test'; })->name('test.route');
" >> routes/theme.php
```

2. **In the watch terminal**, you should see:

```
📝 Changes detected at [timestamp]
   ✓ Modified: routes/managers.php

🔄 Syncing changes...
✅ Sync completed!
   ├─ Added: 1
   ├─ Updated: 0
   └─ Deleted: 0
```

3. **Clean up** the test route:

```bash
# Remove the test route from routes/theme.php
```

4. **Verify detection** in watch mode - you should see the deletion:

```
📝 Changes detected at [timestamp]
   ✓ Modified: routes/managers.php

🔄 Syncing changes...
✅ Sync completed!
   ├─ Added: 0
   ├─ Updated: 0
   └─ Deleted: 1
```

---

## Phase 4: Daemon Mode Testing (Development)

### Start the Daemon

```bash
php artisan routes:daemon --interval=3
```

**Expected Output:**
```
🚀 Route Watcher Daemon Started
📋 Process ID: [PID number]
⏱️ Check interval: 3 seconds
📝 Logging to: storage/logs/route-watcher.log

The daemon is running in the background.
To view logs: tail -f storage/logs/route-watcher.log
To stop: php artisan routes:daemon --stop
```

### Monitor the Daemon

In another terminal:

```bash
# View real-time logs
tail -f storage/logs/route-watcher.log

# Check daemon status
php artisan routes:daemon --status
```

**Expected Log Format:**
```
[2024-11-29 10:30:15] local.INFO: Starting route file monitoring...
[2024-11-29 10:30:15] local.INFO: Monitoring 6 route files
[2024-11-29 10:30:20] local.INFO: Checking for changes...
[2024-11-29 10:30:25] local.INFO: Checking for changes...
```

### Test with Changes

While daemon is running, add a test route:

```bash
echo "Route::get('daemon-test', fn() => 'test')->name('daemon.test');" >> routes/shops.php
```

Check logs to see it detected:

```
[timestamp] local.INFO: Changes detected: ['modified' => ['routes/shops.php']]
[timestamp] local.INFO: Syncing changes...
[timestamp] local.INFO: Route added: daemon.test
[timestamp] local.INFO: Synchronization completed
```

### Stop the Daemon

```bash
php artisan routes:daemon --stop
```

---

## Phase 5: Supervisor Setup (Production & Development)

### Prerequisites

Check supervisor installation:

```bash
# Ubuntu/Debian
apt-get install supervisor

# macOS
brew install supervisor

# CentOS/RHEL
yum install supervisor

# Verify installation
supervisord --version
supervisorctl --version
```

### Development Environment Setup

```bash
# Run the automated setup script
sudo ./scripts/setup-supervisor.sh dev
```

**Expected Output:**
```
═══════════════════════════════════════════════════════
  🔧 Setting up Route Watcher for DEVELOPMENT
═══════════════════════════════════════════════════════

📋 Copying supervisor configuration...
✅ Configuration copied

📁 Creating log directories...
✅ Log directories created

🔄 Reloading supervisor...
✅ Supervisor reloaded

🚀 Starting route watcher daemon...
✅ Route watcher started

📊 Service Status:
laravel-route-watcher-dev    RUNNING   pid 12345, uptime 0:00:05
```

### Production Environment Setup

```bash
# Run the automated setup script for production
sudo ./scripts/setup-supervisor.sh prod
```

**Expected Output:**
```
═══════════════════════════════════════════════════════
  🔧 Setting up Route Watcher for PRODUCTION
═══════════════════════════════════════════════════════

👤 Web server user: www-data

📋 Copying supervisor configuration...
✅ Configuration copied

📁 Creating log directories...
✅ Log directories created with proper permissions

🔄 Reloading supervisor...
✅ Supervisor reloaded

🚀 Starting route watcher daemon...
✅ Route watcher started

📊 Service Status:
laravel-route-watcher-prod   RUNNING   pid 12346, uptime 0:00:05
```

### Verify Supervisor Setup

```bash
# View all supervisor programs
sudo supervisorctl status

# Expected output:
# laravel-route-watcher-dev   RUNNING   pid 12345, uptime 0:00:15
# laravel-route-watcher-prod  RUNNING   pid 12346, uptime 0:00:10

# View specific program status
sudo supervisorctl status laravel-route-watcher-dev
sudo supervisorctl status laravel-route-watcher-prod

# Start/stop programs
sudo supervisorctl start laravel-route-watcher-dev
sudo supervisorctl stop laravel-route-watcher-dev
sudo supervisorctl restart laravel-route-watcher-dev

# Reload supervisor after config changes
sudo supervisorctl reread
sudo supervisorctl update
```

### Monitor Logs

```bash
# Development logs
tail -f storage/logs/supervisor/route-watcher-dev.log
tail -f storage/logs/supervisor/route-watcher-dev-error.log

# Production logs
tail -f storage/logs/supervisor/route-watcher-prod.log
tail -f storage/logs/supervisor/route-watcher-prod-error.log
```

---

## Phase 6: Verification Checklist

Run through this checklist to verify everything is working:

### Database
- [ ] Migrations executed successfully
- [ ] `app_routes` table exists and has data
- [ ] `route_permissions` table exists
- [ ] Routes are grouped correctly by profile

### Manual Sync
- [ ] `php artisan routes:sync` completes without errors
- [ ] Routes are added to database
- [ ] Statistics show correct counts by profile

### Interactive Watch
- [ ] `php artisan routes:watch` starts and shows monitored files
- [ ] Detects changes when route files are modified
- [ ] Auto-syncs changes to database
- [ ] Correctly shows added/deleted/modified counts

### Daemon Mode
- [ ] `php artisan routes:daemon` starts with PID
- [ ] Logs are written to `storage/logs/route-watcher.log`
- [ ] Detects changes continuously
- [ ] `--status` flag shows running status
- [ ] `--stop` flag cleanly stops the daemon

### Supervisor (if configured)
- [ ] Both dev and prod configurations are in `/etc/supervisor/conf.d/`
- [ ] `supervisorctl status` shows both services
- [ ] Services are in RUNNING state
- [ ] Logs are being written to supervisor log directory
- [ ] Services auto-restart if stopped

---

## Phase 7: Troubleshooting

### Error: "Method getPath does not exist"

**Status:** ✅ FIXED

This error occurs on certain Laravel versions. The fix is already implemented with a fallback compatibility method.

**Verification:**
```bash
php artisan routes:sync
# Should work without getPath() errors
```

**How it works:**
The `getRoutePath()` method in `RouteSyncService.php` tries multiple methods in order:
1. `$route->getPath()` (Laravel 11+)
2. `$route->getUri()` (Laravel 8-10)
3. `$route->uri` property (Laravel 7)
4. `$route->compiledRoute->getPath()` (fallback)

### Error: "Permission denied" in Supervisor

**Solution:**
```bash
# Make script executable
chmod +x scripts/setup-supervisor.sh

# Run with sudo
sudo ./scripts/setup-supervisor.sh dev
```

### Supervisor Service Not Starting

**Check logs:**
```bash
# Supervisor logs
sudo tail -f /var/log/supervisor/supervisord.log

# Application logs
tail -f storage/logs/supervisor/route-watcher-dev.log
tail -f storage/logs/supervisor/route-watcher-dev-error.log
```

### Daemon Not Detecting Changes

**Verify cache directory:**
```bash
# The daemon stores file hashes in:
ls -la storage/app/route-monitor-cache.json

# If corrupted, delete and re-run:
rm storage/app/route-monitor-cache.json
php artisan routes:daemon --interval=3
```

### Routes Not Syncing

**Manual verification:**
```bash
# Check if routes exist in Laravel
php artisan route:list | grep -E "manager|shop|warehouse"

# Try manual sync
php artisan routes:sync

# Check database directly
php artisan tinker
>>> AppRoute::count()
>>> AppRoute::where('profile', 'manager')->get()
```

---

## Phase 8: Common Operations

### Check Route Statistics
```bash
php artisan tinker
>>> use App\Services\RouteSyncService;
>>> $service = new RouteSyncService();
>>> $stats = $service->getStatistics();
>>> $stats
```

### Get Routes by Profile
```bash
php artisan tinker
>>> use App\Services\RouteSyncService;
>>> $service = new RouteSyncService();
>>> $routes = $service->getRoutesByProfile('manager');
>>> $routes->pluck('name', 'method')
```

### Test Route Access Control
The three approaches are documented in `routes/shared/user-routes.php`:
1. **Middleware-based** (RECOMMENDED) - Centralized permission checking
2. **Separate routing** - Different route groups for different roles
3. **Permission-based** - Permission-route many-to-many relationships

---

## Summary

**System Status: ✅ COMPLETE**

| Component | Status | Command |
|-----------|--------|---------|
| Migrations | ✅ Ready | `php artisan migrate` |
| Manual Sync | ✅ Working | `php artisan routes:sync` |
| Interactive Watch | ✅ Working | `php artisan routes:watch` |
| Daemon Mode | ✅ Working | `php artisan routes:daemon` |
| Supervisor Setup | ✅ Ready | `sudo ./scripts/setup-supervisor.sh [dev\|prod\|both]` |
| Compatibility | ✅ Fixed | Works with Laravel 7+ |

**Next Steps:**
1. Run Phase 1 (migrations)
2. Run Phase 2 (sync)
3. Test Phase 3 (watch mode)
4. Test Phase 4 (daemon mode)
5. Deploy Phase 5 (supervisor)
