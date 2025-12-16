# Route File Watcher - Real-Time Monitoring Guide

## 🎯 Overview

The **Route File Watcher** automatically monitors your route files for changes and triggers synchronization without any manual intervention. Changes are detected instantly and routes are synced to the database automatically.

---

## ✨ Features

✅ **Real-time Monitoring** - Detects changes instantly
✅ **Automatic Sync** - Triggers `routes:sync` automatically
✅ **File Tracking** - Detects added, modified, and deleted route files
✅ **Daemon Mode** - Runs as background process
✅ **Interactive Mode** - Watch in terminal with live output
✅ **Detailed Logging** - All changes logged to file
✅ **Cross-Platform** - Works on Windows, Linux, macOS

---

## 🚀 Quick Start

### Option 1: Interactive Mode (Recommended for Development)

Watch route files in your terminal with real-time output:

```bash
php artisan routes:watch
```

**Output example:**
```
🔍 Route file watcher started (checking every 5s)
Press Ctrl+C to stop monitoring
────────────────────────────────────────────────────────

⚠️  Route file changes detected at 2024-11-29 14:23:15
✅ Modified files: routes/managers.php
Syncing routes with database...

📊 Sync Results:
   Total routes processed: 234
   ⟳ Updated: 2
      • manager.users.index
      • manager.users.create
────────────────────────────────────────────────────────
```

### Option 2: Daemon Mode (For Production)

Run as background service that keeps watching:

```bash
# Start daemon
php artisan routes:daemon

# Check status
php artisan routes:daemon --status

# Stop daemon
php artisan routes:daemon --stop
```

---

## 📋 Commands Reference

### `routes:watch` - Interactive Watcher

```bash
# Watch with default 5-second interval
php artisan routes:watch

# Watch with custom interval (1-60 seconds)
php artisan routes:watch --interval=3

# Watch specific file in addition to defaults
php artisan routes:watch --add=routes/custom.php
```

**Options:**
- `--interval=N` - Check interval in seconds (default: 5)
- `--add=path` - Add additional file to monitor

### `routes:daemon` - Background Daemon

```bash
# Start daemon with default 5-second interval
php artisan routes:daemon

# Start with custom interval
php artisan routes:daemon --interval=3

# Check if daemon is running
php artisan routes:daemon --status

# Stop the daemon
php artisan routes:daemon --stop
```

**Options:**
- `--interval=N` - Check interval in seconds (default: 5)
- `--status` - Show daemon status and recent logs
- `--stop` - Stop the running daemon

### `routes:sync` - Manual Synchronization

```bash
# Manual sync (still available)
php artisan routes:sync
```

---

## 📁 Monitored Files

By default, the watcher monitors these files:

```
routes/managers.php
routes/callcenters.php
routes/shops.php
routes/warehouses.php
routes/administratives.php
routes/returns.php
```

To add custom files:

```bash
php artisan routes:watch --add=routes/custom.php
```

---

## 🔄 How It Works

### Detection Mechanism

The watcher uses **file hashing** to detect changes:

```
1. Reads each route file's content
2. Calculates MD5 hash of content + modification time
3. Compares with cached hashes
4. Detects:
   ✅ Added files (new hash)
   ⟳ Modified files (hash changed)
   ❌ Deleted files (hash missing)
```

### When Changes Are Detected

```
File Change Detected
        ↓
Calculate New Hash
        ↓
Compare with Cache
        ↓
If Different:
   ├─ Log the change
   ├─ Display notification
   ├─ Run routes:sync automatically
   └─ Update cache
        ↓
Continue Monitoring
```

---

## 💻 Usage Scenarios

### Scenario 1: Development with Interactive Watcher

Perfect for local development where you want to see changes in real-time:

```bash
# Terminal 1: Start your development server
php artisan serve

# Terminal 2: Run the watcher
php artisan routes:watch

# Now edit route files in your IDE
# Changes appear in Terminal 2 automatically!
```

**Workflow:**
1. You add new routes to `routes/managers.php`
2. Watcher detects the change (within 5 seconds)
3. Automatically runs `routes:sync`
4. You see confirmation in your terminal

---

### Scenario 2: Production with Daemon

For production, run as a daemon that keeps monitoring:

```bash
# Start at server boot (add to systemd, supervisord, etc.)
php artisan routes:daemon

# Later, check if it's still running
php artisan routes:daemon --status

# When you deploy new code with route changes
# The daemon automatically syncs them!

# When you need to stop
php artisan routes:daemon --stop
```

---

### Scenario 3: Systemd Service (Linux)

Create a systemd service file for automatic daemon management:

**File: `/etc/systemd/system/laravel-route-watcher.service`**

```ini
[Unit]
Description=Laravel Route File Watcher
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/laravel/app
ExecStart=/usr/bin/php /path/to/your/laravel/app/artisan routes:daemon --interval=5
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable laravel-route-watcher
sudo systemctl start laravel-route-watcher
sudo systemctl status laravel-route-watcher
```

---

### Scenario 4: Supervisord (Production Recommended)

**File: `/etc/supervisor/conf.d/laravel-route-watcher.conf`**

```ini
[program:laravel-route-watcher]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/laravel/app/artisan routes:daemon --interval=5
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/laravel/app/storage/logs/route-watcher.log
user=www-data
```

Enable and start:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-route-watcher:*
```

---

## 📊 Monitoring & Logs

### View Daemon Status

```bash
php artisan routes:daemon --status
```

Output:
```
╔════════════════════════════════════════════╗
║    🔍 ROUTE WATCHER DAEMON STATUS          ║
╚════════════════════════════════════════════╝

✅ Status: RUNNING (PID: 12345)

Log file: /path/to/storage/logs/route-watcher.log
Size: 2.45 MB

Recent logs (last 5 lines):
  2024-11-29 14:23:15 Route files changed - Auto-sync completed
  2024-11-29 14:23:10 Route file changes detected
  2024-11-29 14:18:05 No route changes detected
  2024-11-29 14:13:00 Route file changes detected
  2024-11-29 14:08:15 Auto-sync completed
```

### View Logs in Real-Time

```bash
# Unix/Linux/macOS
tail -f storage/logs/route-watcher.log

# Windows (using PowerShell)
Get-Content storage/logs/route-watcher.log -Wait
```

### Log File Locations

- **Interactive watcher logs:** `storage/logs/laravel.log`
- **Daemon logs:** `storage/logs/route-watcher.log`
- **Daemon PID file:** `storage/route-watcher.pid`
- **Cache file:** `storage/app/route-monitor-cache.json`

---

## 🔧 Configuration

### Check Interval

The default interval is **5 seconds**, which is optimal for most use cases:

```bash
# Check every 3 seconds (faster response, more CPU usage)
php artisan routes:watch --interval=3

# Check every 10 seconds (slower response, less CPU usage)
php artisan routes:watch --interval=10
```

**Recommendations:**
- **Development:** 3-5 seconds (fast feedback)
- **Production:** 10-30 seconds (lower resource usage)
- **Very Active:** < 3 seconds (instant updates)

### Add Custom Files

If you have custom route files outside the default ones:

```bash
php artisan routes:watch --add=routes/api.php --add=routes/webhooks.php
```

Or for daemon:

```bash
php artisan routes:daemon --add=routes/custom.php
```

---

## ⚠️ Important Notes

### Performance Considerations

- **Small projects:** Minimal impact, safe to run continuously
- **Large projects:** Interval should be 10+ seconds to avoid overhead
- **Production:** Use supervisord/systemd for automatic restarts

### Cache Management

The watcher maintains a cache of file hashes:

```bash
# Location: storage/app/route-monitor-cache.json
# Automatically updated when changes detected
# Cleared on cache:clear
```

If you need to reset the cache:

```bash
php artisan cache:clear
# OR manually delete
rm storage/app/route-monitor-cache.json
```

### Process Management

**On Development Machine:**
- Stop with Ctrl+C
- Restart with same command

**On Production:**
```bash
# Stop
php artisan routes:daemon --stop

# Start
php artisan routes:daemon

# Restart
php artisan routes:daemon --stop && php artisan routes:daemon
```

---

## 🐛 Troubleshooting

### Daemon not detecting changes

1. **Check if daemon is running:**
   ```bash
   php artisan routes:daemon --status
   ```

2. **Check recent logs:**
   ```bash
   tail -f storage/logs/route-watcher.log
   ```

3. **Restart the daemon:**
   ```bash
   php artisan routes:daemon --stop
   php artisan routes:daemon
   ```

### Routes not syncing

1. **Check file permissions** - Watcher needs read access to route files
2. **Check disk space** - May fail if storage is full
3. **Check database connection** - Must be able to write to database
4. **Run manual sync** to test:
   ```bash
   php artisan routes:sync
   ```

### High CPU usage

1. **Increase interval:**
   ```bash
   php artisan routes:daemon --interval=30
   ```

2. **Check log file size:**
   ```bash
   ls -lh storage/logs/route-watcher.log
   ```

3. **Rotate logs if needed:**
   ```bash
   > storage/logs/route-watcher.log
   ```

### Permission denied error

On Linux/macOS, ensure proper permissions:

```bash
# For current user
chmod u+w storage/
chmod u+w storage/app/
chmod u+w storage/logs/

# For daemon with www-data user
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/
```

---

## 📈 Best Practices

1. ✅ **Use interval of 5-10 seconds** for optimal balance
2. ✅ **Monitor logs regularly** to detect issues early
3. ✅ **Use supervisord/systemd** for production daemon
4. ✅ **Keep cache file** - Let the watcher manage it
5. ✅ **Check status periodically** in production
6. ✅ **Restart daemon monthly** to ensure stability
7. ✅ **Archive old logs** to save disk space

---

## 🎓 Summary

| Use Case | Command | Benefits |
|----------|---------|----------|
| **Local Development** | `routes:watch` | Real-time feedback, interactive |
| **Production** | `routes:daemon` | Background, automatic restart |
| **Testing** | `routes:sync` | Manual sync, useful for CI/CD |
| **Monitoring** | `routes:daemon --status` | Health check, log viewing |

---

## 📞 Support

If issues persist:

1. Check logs: `storage/logs/route-watcher.log`
2. Verify routes: `php artisan route:list`
3. Manual sync: `php artisan routes:sync`
4. Review cache: `storage/app/route-monitor-cache.json`

---

**🚀 Ready to go!** Your routes will now sync automatically whenever files change!
