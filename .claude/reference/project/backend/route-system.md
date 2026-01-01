# Route Synchronization System - Complete Documentation

**Status: ✅ Production Ready**
**Version: 1.0**
**Last Updated: November 29, 2024**

---

## 🎯 What Is This?

A complete, production-grade system that automatically detects when developers add or remove routes from route files, syncs them to a database, and enforces role-based access control.

**In Plain English:** If you add a new route to `routes/managers.php`, it automatically appears in the database within 3-15 seconds (configurable). No manual intervention needed.

---

## ⚡ Quick Start (5 Minutes)

```bash
# 1. Create database tables
php artisan migrate

# 2. Sync existing routes
php artisan routes:sync

# 3. Start watching for changes
php artisan routes:watch

# 4. (Optional) Set up supervisor for production
sudo ./scripts/setup-supervisor.sh prod
```

**That's it!** The system is now running.

For detailed instructions, see **`QUICK_START.md`**

---

## 📚 Documentation Guide

### For Different Needs

**👤 "I just want to get started"**
→ Read: `QUICK_START.md` (5 min read)

**🔧 "I need to set this up properly"**
→ Read: `SETUP_AND_VERIFICATION_GUIDE.md` (20 min read)

**🚀 "I need to deploy to production"**
→ Read: `SUPERVISOR_OPERATIONS_GUIDE.md` (15 min read)

**🧠 "I want to understand how it works"**
→ Read: `SYSTEM_ARCHITECTURE.md` (30 min read)

**📊 "What's been completed?"**
→ Read: `SYSTEM_STATUS.md` (10 min read)

**🐛 "Something's broken, help me fix it"**
→ Read: `SUPERVISOR_OPERATIONS_GUIDE.md` → Part 5: Troubleshooting

**🔍 "Tell me about route synchronization"**
→ Read: `ROUTE_SYNC_GUIDE.md` (15 min read)

**⌚ "Tell me about the file watcher"**
→ Read: `ROUTE_WATCHER_GUIDE.md` (15 min read)

**✅ "I heard about a compatibility issue"**
→ Read: `COMPATIBILITY_FIXES.md` (5 min read)

---

## 📁 File Structure

```
Project Root/
│
├── 📄 README_ROUTE_SYSTEM.md (← You are here)
├── 📄 QUICK_START.md
├── 📄 SETUP_AND_VERIFICATION_GUIDE.md
├── 📄 SUPERVISOR_OPERATIONS_GUIDE.md
├── 📄 SYSTEM_ARCHITECTURE.md
├── 📄 SYSTEM_STATUS.md
├── 📄 ROUTE_SYNC_GUIDE.md
├── 📄 ROUTE_WATCHER_GUIDE.md
├── 📄 COMPATIBILITY_FIXES.md
│
├── app/
│   ├── Services/
│   │   ├── RouteSyncService.php
│   │   └── RouteFileWatcherService.php
│   ├── Console/Commands/
│   │   ├── SyncRoutesCommand.php
│   │   ├── WatchRoutesCommand.php
│   │   └── StartRouteWatcherDaemonCommand.php
│   ├── Models/
│   │   ├── AppRoute.php
│   │   └── RoutePermission.php
│   └── Http/Middleware/
│       └── CheckRolesAndPermissions.php
│
├── config/supervisor/
│   ├── laravel-route-watcher-dev.conf
│   └── laravel-route-watcher-prod.conf
│
├── scripts/
│   └── setup-supervisor.sh
│
├── database/migrations/
│   ├── 2024_11_29_create_app_routes_table.php
│   └── 2024_11_29_create_route_permissions_table.php
│
└── storage/
    ├── logs/supervisor/
    │   ├── route-watcher-dev.log
    │   └── route-watcher-prod.log
    └── app/
        └── route-monitor-cache.json
```

---

## 🚀 How It Works

### Simple Overview

```
Developer adds route to file
        ↓
File Monitor detects change (every 3-15 seconds)
        ↓
Sync Service extracts routes
        ↓
Routes are added to database
        ↓
Middleware enforces permissions
        ↓
Users can access route (if authorized)
```

### Detailed Flow

1. **Developer adds a route:**
   ```php
   // routes/theme.php
   Route::resource('reports', ReportController::class);
   ```

2. **File watcher detects change** (within 3-15 seconds):
   - Calculates file hash
   - Compares with previous hash
   - Detects it's different

3. **Sync service extracts routes:**
   - Reads Laravel router
   - Gets route metadata
   - Generates unique hash

4. **Database is updated:**
   - New routes inserted
   - Changed routes updated
   - Deleted routes removed

5. **Permissions enforced:**
   - Middleware checks user role
   - Verifies permissions
   - Allows/denies access

---

## 🛠️ Core Commands

### Manual Sync
```bash
# One-time synchronization of all routes
php artisan routes:sync
```

### Interactive Watch
```bash
# Watch for changes in real-time (development)
php artisan routes:watch --interval=5
```

### Background Daemon
```bash
# Run as background daemon
php artisan routes:daemon --interval=3

# Check if running
php artisan routes:daemon --status

# Stop daemon
php artisan routes:daemon --stop
```

### Supervisor Setup
```bash
# Automated setup for development
sudo ./scripts/setup-supervisor.sh dev

# Automated setup for production
sudo ./scripts/setup-supervisor.sh prod

# Both environments
sudo ./scripts/setup-supervisor.sh both
```

---

## 📊 What Gets Synced

The system tracks:
- ✅ Route name (e.g., 'users.index')
- ✅ Route path (e.g., '/users')
- ✅ HTTP methods (GET, POST, PUT, DELETE, PATCH)
- ✅ Controller and action
- ✅ Required middleware
- ✅ Authentication requirement
- ✅ User profile/role
- ✅ Unique hash for deduplication

Routes that are **skipped**:
- ❌ Routes without names (unnamed routes)
- ❌ Laravel debug routes (debugbar, ignition)
- ❌ API routes (by design)
- ❌ Middleware-only routes (no controller)

---

## 🔐 Access Control

Three approaches to protect routes:

### 1️⃣ Middleware-Based (RECOMMENDED)
```php
Route::middleware(['auth', 'check.roles.permissions:manager'])
    ->group(function () {
        Route::resource('users', UserController::class);
    });
```

**Pros:** Centralized, single point of control
**Use:** Most route groups

### 2️⃣ Separate Routing
```php
// Separate files per role
routes/manager-users.php
routes/admin-users.php
```

**Pros:** Clear separation by role
**Use:** Significantly different logic per role

### 3️⃣ Permission-Based
```php
Route::resource('users', UserController::class)
    ->middleware('check.permissions:users.view');
```

**Pros:** Fine-grained control
**Use:** Complex permission requirements

---

## 🧬 System Components

### Services

| Service | Purpose | Location |
|---------|---------|----------|
| **RouteSyncService** | Extracts and syncs routes | `app/Services/RouteSyncService.php` |
| **RouteFileWatcherService** | Monitors file changes | `app/Services/RouteFileWatcherService.php` |

### Models

| Model | Purpose | Location |
|-------|---------|----------|
| **AppRoute** | Stores route metadata | `app/Models/AppRoute.php` |
| **RoutePermission** | Links routes to permissions | `app/Models/RoutePermission.php` |

### Commands

| Command | Purpose | Location |
|---------|---------|----------|
| **SyncRoutesCommand** | Manual sync | `app/Console/Commands/SyncRoutesCommand.php` |
| **WatchRoutesCommand** | Interactive watch | `app/Console/Commands/WatchRoutesCommand.php` |
| **StartRouteWatcherDaemonCommand** | Background daemon | `app/Console/Commands/StartRouteWatcherDaemonCommand.php` |

### Middleware

| Middleware | Purpose | Location |
|-----------|---------|----------|
| **CheckRolesAndPermissions** | Access control | `app/Http/Middleware/CheckRolesAndPermissions.php` |

---

## ⚙️ Configuration

### Development Configuration

**Interval:** 3 seconds (for quick feedback)
**User:** Current logged-in user
**Logs:** 10MB max, 5 backups

```bash
# Start dev watcher
php artisan routes:watch

# Or via supervisor
sudo supervisorctl start laravel-route-watcher-dev
```

### Production Configuration

**Interval:** 15 seconds (balanced load)
**User:** www-data
**Logs:** 50MB max, 10 backups

```bash
# Setup supervisor
sudo ./scripts/setup-supervisor.sh prod

# Check status
sudo supervisorctl status
```

---

## 📋 Deployment Modes

### Mode 1: Manual (for testing)
```bash
php artisan routes:sync
```
One-time sync, useful for testing.

### Mode 2: Interactive (for development)
```bash
php artisan routes:watch
```
Real-time monitoring with terminal output.

### Mode 3: Daemon (for continuous operation)
```bash
php artisan routes:daemon --interval=3
```
Background process, logs to file.

### Mode 4: Supervisor (for production)
```bash
sudo ./scripts/setup-supervisor.sh prod
```
Managed by Supervisor, auto-restart, persistent.

---

## 📊 Database Schema

### app_routes Table
Stores route information:
- Route name, path, methods
- Controller and action
- Profile/role
- Required middleware
- Authentication status
- Unique hash

### route_permissions Table
Links routes to permissions:
- route_id (FK to app_routes)
- permission_id (FK to permissions)
- Enables many-to-many relationships

---

## 🔍 Monitoring & Debugging

### Check Status
```bash
# Manual
php artisan tinker
>>> AppRoute::count()

# Supervisor
sudo supervisorctl status

# View logs
tail -f storage/logs/supervisor/route-watcher-prod.log
```

### Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Routes not syncing | Check daemon is running: `supervisorctl status` |
| High CPU usage | Increase check interval (15s for prod) |
| Permission denied | Run migrations: `php artisan migrate` |
| Supervisor won't start | Rerun setup: `sudo ./scripts/setup-supervisor.sh prod` |

For more solutions, see `SUPERVISOR_OPERATIONS_GUIDE.md` Part 5.

---

## 🎓 Learning Path

### Day 1: Get Started
1. Read: `QUICK_START.md`
2. Run: `php artisan migrate`
3. Run: `php artisan routes:sync`
4. Test: `php artisan routes:watch`

### Day 2: Understand the System
1. Read: `SYSTEM_ARCHITECTURE.md`
2. Read: `ROUTE_SYNC_GUIDE.md`
3. Explore: Database tables
4. Explore: Console commands

### Day 3: Deploy to Production
1. Read: `SUPERVISOR_OPERATIONS_GUIDE.md`
2. Read: `SETUP_AND_VERIFICATION_GUIDE.md` Phase 5
3. Run: `sudo ./scripts/setup-supervisor.sh prod`
4. Verify: `sudo supervisorctl status`

### Day 4+: Operate & Maintain
1. Monitor: Supervisor logs
2. Maintain: Weekly health checks
3. Optimize: Adjust intervals if needed

---

## ✅ Checklist: Before Going Live

- [ ] Run migrations: `php artisan migrate`
- [ ] Test sync: `php artisan routes:sync`
- [ ] Test watch: `php artisan routes:watch`
- [ ] Read supervisor guide
- [ ] Set up supervisor: `sudo ./scripts/setup-supervisor.sh prod`
- [ ] Verify: `sudo supervisorctl status`
- [ ] Monitor logs: `tail -f storage/logs/supervisor/route-watcher-prod.log`
- [ ] Document any customizations

---

## 🆘 Troubleshooting

### The System Won't Start

1. Check migrations were run:
   ```bash
   php artisan migrate
   ```

2. Check Laravel routes exist:
   ```bash
   php artisan route:list
   ```

3. Check logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Supervisor Service Won't Start

1. Check supervisor is installed:
   ```bash
   supervisord --version
   ```

2. Rerun setup:
   ```bash
   sudo ./scripts/setup-supervisor.sh prod
   ```

3. Check supervisor logs:
   ```bash
   sudo tail -f /var/log/supervisor/supervisord.log
   ```

### Routes Not Being Detected

1. Check daemon is running:
   ```bash
   sudo supervisorctl status laravel-route-watcher-prod
   ```

2. Clear cache:
   ```bash
   rm storage/app/route-monitor-cache.json
   ```

3. Restart:
   ```bash
   sudo supervisorctl restart laravel-route-watcher-prod
   ```

**For comprehensive troubleshooting:** See `SUPERVISOR_OPERATIONS_GUIDE.md` Part 5

---

## 📞 Support & Documentation

| Need | Document |
|------|----------|
| Quick answers | `QUICK_START.md` |
| Setup help | `SETUP_AND_VERIFICATION_GUIDE.md` |
| Supervisor issues | `SUPERVISOR_OPERATIONS_GUIDE.md` |
| Technical details | `SYSTEM_ARCHITECTURE.md` |
| Completion status | `SYSTEM_STATUS.md` |
| Route sync details | `ROUTE_SYNC_GUIDE.md` |
| File watcher details | `ROUTE_WATCHER_GUIDE.md` |
| Version issues | `COMPATIBILITY_FIXES.md` |

---

## 🎉 Summary

You now have a complete, production-ready route synchronization system that:

✅ Automatically detects route file changes
✅ Syncs routes to database within 3-15 seconds
✅ Supports development and production deployments
✅ Integrates with Spatie roles/permissions
✅ Provides comprehensive logging and auditing
✅ Includes automated setup scripts
✅ Has 9 documentation files
✅ Is fully tested and documented

**Ready to deploy!**

---

## 🚀 Next Steps

1. **Read:** `QUICK_START.md`
2. **Setup:** Run migrations and sync
3. **Test:** Try watch mode
4. **Deploy:** Use supervisor setup script
5. **Monitor:** Check logs regularly

---

## 📝 Version History

| Version | Date | Status |
|---------|------|--------|
| 1.0 | Nov 29, 2024 | ✅ Complete & Production-Ready |

---

## 📄 License

This system is part of the Alsernet project.

---

**Questions?** Check the relevant documentation file above.

**Ready to get started?** Go to `QUICK_START.md` →
