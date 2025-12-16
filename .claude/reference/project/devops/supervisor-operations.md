# Supervisor Operations & Troubleshooting Guide

## Quick Reference

### Essential Commands

```bash
# Check supervisor status
sudo supervisorctl status

# View specific service status
sudo supervisorctl status laravel-route-watcher-dev
sudo supervisorctl status laravel-route-watcher-prod

# Start service
sudo supervisorctl start laravel-route-watcher-dev

# Stop service
sudo supervisorctl stop laravel-route-watcher-dev

# Restart service
sudo supervisorctl restart laravel-route-watcher-dev

# Reload supervisor after config changes
sudo supervisorctl reread
sudo supervisorctl update

# View real-time logs
tail -f storage/logs/supervisor/route-watcher-dev.log

# View errors
tail -f storage/logs/supervisor/route-watcher-dev-error.log
```

---

## Part 1: Setup

### Automated Setup (Recommended)

The `setup-supervisor.sh` script automates all configuration:

```bash
# For development environment
sudo ./scripts/setup-supervisor.sh dev

# For production environment
sudo ./scripts/setup-supervisor.sh prod

# For both environments
sudo ./scripts/setup-supervisor.sh both
```

**What the script does:**
1. ✅ Validates supervisor is installed
2. ✅ Copies configuration files to `/etc/supervisor/conf.d/`
3. ✅ Substitutes environment variables (Laravel root path, user)
4. ✅ Creates log directories with proper permissions
5. ✅ Reloads supervisor configuration
6. ✅ Starts the services
7. ✅ Displays status and helpful commands

### Manual Setup (If Needed)

If the script fails, you can set up manually:

```bash
# 1. Copy configuration file
sudo cp config/supervisor/laravel-route-watcher-dev.conf \
         /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# 2. Replace environment variables
LARAVEL_ROOT=$(pwd)
sudo sed -i "s|%(ENV_LARAVEL_ROOT)s|${LARAVEL_ROOT}|g" \
    /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# 3. Create log directory
mkdir -p storage/logs/supervisor
chmod 755 storage/logs/supervisor

# 4. Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update

# 5. Start the service
sudo supervisorctl start laravel-route-watcher-dev
```

---

## Part 2: Configuration Files

### Development Configuration

**Location:** `/etc/supervisor/conf.d/laravel-route-watcher-dev.conf`

**Key Settings:**
```ini
command=php /path/to/artisan routes:daemon --interval=3
user=yourname                    # Current logged-in user
autostart=true                   # Start on supervisor startup
autorestart=true                 # Auto-restart if crashes
numprocs=1                        # Run single instance
priority=999                      # Start order in supervisor
stdout_logfile_maxbytes=10MB      # Smaller logs for dev
stdout_logfile_backups=5          # Keep 5 backup logs
```

**When to use:**
- Local development
- Testing route changes frequently
- Need quick feedback (3-second check interval)

### Production Configuration

**Location:** `/etc/supervisor/conf.d/laravel-route-watcher-prod.conf`

**Key Settings:**
```ini
command=php /path/to/artisan routes:daemon --interval=15
user=www-data                    # Web server user
autostart=true                   # Start on supervisor startup
autorestart=true                 # Auto-restart if crashes
numprocs=1                        # Run single instance
priority=999                      # Start order in supervisor
stdout_logfile_maxbytes=50MB      # Larger logs for prod
stdout_logfile_backups=10         # Keep 10 backup logs
startretries=3                    # Retry 3 times before giving up
startsecs=10                      # Wait 10s before marking as started
```

**When to use:**
- Production environments
- Stable route configurations
- Need balanced CPU usage (15-second check interval)

### Customizing Configuration

To modify check interval:

```bash
# Edit the configuration file
sudo nano /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# Change this line:
# command=php /path/to/artisan routes:daemon --interval=3

# To a different interval (in seconds):
# command=php /path/to/artisan routes:daemon --interval=5

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Restart service
sudo supervisorctl restart laravel-route-watcher-dev
```

---

## Part 3: Monitoring & Operations

### View Service Status

```bash
# View all supervisor programs
sudo supervisorctl status

# Example output:
# laravel-route-watcher-dev    RUNNING   pid 12345, uptime 0:00:15
# laravel-route-watcher-prod   RUNNING   pid 12346, uptime 0:00:10

# View specific service
sudo supervisorctl status laravel-route-watcher-dev

# Example output:
# laravel-route-watcher-dev RUNNING   pid 12345, uptime 0:00:15
```

### Service Status Meanings

| Status | Meaning | Action |
|--------|---------|--------|
| RUNNING | Service is active and working | None - all good! |
| STOPPED | Service is stopped | `supervisorctl start [service]` |
| STOPPING | Service is shutting down | Wait a moment, then check status |
| STARTING | Service is starting up | Wait a moment, then check status |
| BACKOFF | Crashed and retrying startup | Check logs for errors |
| FATAL | Crashed and exceeded retry limit | Fix issue, then restart |
| EXITED | Exited normally but not respawning | Check configuration |
| UNKNOWN | Cannot determine status | Restart supervisor or service |

### Control Services

```bash
# Start service
sudo supervisorctl start laravel-route-watcher-dev

# Stop service gracefully
sudo supervisorctl stop laravel-route-watcher-dev

# Restart service
sudo supervisorctl restart laravel-route-watcher-dev

# Start/stop/restart all services
sudo supervisorctl start all
sudo supervisorctl stop all
sudo supervisorctl restart all

# Restart supervisor daemon itself (if needed)
sudo systemctl restart supervisor
```

---

## Part 4: Viewing Logs

### Real-Time Log Monitoring

```bash
# Development logs
tail -f storage/logs/supervisor/route-watcher-dev.log

# Production logs
tail -f storage/logs/supervisor/route-watcher-prod.log

# Error logs (if any)
tail -f storage/logs/supervisor/route-watcher-dev-error.log
tail -f storage/logs/supervisor/route-watcher-prod-error.log

# Show last 50 lines
tail -50 storage/logs/supervisor/route-watcher-dev.log

# Follow with grep (show only errors)
tail -f storage/logs/supervisor/route-watcher-dev.log | grep -i error
```

### Log Rotation

Supervisor automatically rotates logs based on configuration:

**Development:**
- Max file size: 10MB
- Keep 5 backup files

**Production:**
- Max file size: 50MB
- Keep 10 backup files

Old logs are automatically compressed to `.1`, `.2`, etc.

---

## Part 5: Troubleshooting

### Problem: Service Won't Start

**Diagnosis:**
```bash
# Check service status
sudo supervisorctl status laravel-route-watcher-dev

# Check supervisor logs
sudo tail -f /var/log/supervisor/supervisord.log

# Check application logs
tail -f storage/logs/supervisor/route-watcher-dev-error.log
```

**Common Causes & Solutions:**

**1. Config file syntax error**
```bash
# Validate config syntax
sudo supervisord -c /etc/supervisor/supervisord.conf -d
sudo supervisorctl reread

# If errors, check config:
sudo cat /etc/supervisor/conf.d/laravel-route-watcher-dev.conf
```

**2. Log directory doesn't exist**
```bash
# Create log directory
mkdir -p storage/logs/supervisor
chmod 755 storage/logs/supervisor

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
```

**3. Path variables not substituted**
```bash
# Check if paths contain %(ENV_LARAVEL_ROOT)s
sudo grep "%(ENV_" /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# If found, re-run setup script
sudo ./scripts/setup-supervisor.sh dev
```

**4. Permission issues**
```bash
# Development: User doesn't have permissions
# Fix:
sudo chown -R $USER:$USER storage/logs/supervisor
chmod 755 storage/logs/supervisor

# Production: www-data user doesn't have permissions
# Fix:
sudo chown -R www-data:www-data storage/logs/supervisor
chmod 755 storage/logs/supervisor
```

### Problem: Service Keeps Crashing (BACKOFF/FATAL)

**Diagnosis:**
```bash
# Check error log for actual error
tail -50 storage/logs/supervisor/route-watcher-dev-error.log

# Check main log
tail -50 storage/logs/supervisor/route-watcher-dev.log

# Check supervisor logs
sudo tail -f /var/log/supervisor/supervisord.log
```

**Common Causes:**

**1. PHP not found**
```bash
# Verify PHP path
which php

# Check config has correct path
sudo grep "command=" /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# If wrong, edit config:
sudo nano /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# Then reload:
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-route-watcher-dev
```

**2. Laravel app error**
```bash
# Test running the command manually
php artisan routes:daemon --interval=3

# Check for missing dependencies
php artisan tinker
>>> exit

# Check Laravel logs
tail -f storage/logs/laravel.log
```

**3. Port already in use**
```bash
# This shouldn't happen for this daemon, but check:
sudo netstat -tulpn | grep LISTEN
```

### Problem: Not Detecting Route Changes

**Diagnosis:**
```bash
# 1. Verify daemon is running
sudo supervisorctl status laravel-route-watcher-dev
# Should show: RUNNING pid XXXX, uptime 0:00:XX

# 2. Check logs for detection messages
tail -f storage/logs/supervisor/route-watcher-dev.log

# 3. Manually test sync
php artisan routes:sync
```

**Solutions:**

**1. Cache might be corrupted**
```bash
# Delete cache file
rm storage/app/route-monitor-cache.json

# Restart daemon
sudo supervisorctl restart laravel-route-watcher-dev

# Check logs for re-initialization
tail -f storage/logs/supervisor/route-watcher-dev.log
```

**2. File permissions**
```bash
# Ensure daemon can read route files
ls -la routes/

# Ensure daemon can write to cache
ls -la storage/app/

# Fix if needed
chmod 644 routes/*.php
chmod 755 storage/app
```

**3. Wrong interval**
```bash
# Check interval setting
sudo grep "interval=" /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# If too long (e.g., 3600), edit to shorter interval:
sudo nano /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# Change interval and reload
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-route-watcher-dev
```

### Problem: High CPU Usage

**Diagnosis:**
```bash
# Monitor CPU usage
ps aux | grep php

# Check interval setting
sudo grep "interval=" /etc/supervisor/conf.d/laravel-route-watcher-prod.conf
```

**Solutions:**

**1. Increase check interval**
```bash
# Production: Change from 15 to 30 seconds
sudo nano /etc/supervisor/conf.d/laravel-route-watcher-prod.conf

# Change:
# command=php /path/to/artisan routes:daemon --interval=15
# To:
# command=php /path/to/artisan routes:daemon --interval=30

# Reload
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-route-watcher-prod
```

**2. Verify no infinite loops**
```bash
# Check application logs
tail -100 storage/logs/supervisor/route-watcher-prod.log | grep -i error
```

### Problem: Logs Getting Too Large

**Manual Log Cleanup:**
```bash
# Clear logs but keep 50 most recent lines
tail -50 storage/logs/supervisor/route-watcher-dev.log > /tmp/temp.log
mv /tmp/temp.log storage/logs/supervisor/route-watcher-dev.log

# Or compress old logs
gzip storage/logs/supervisor/route-watcher-dev.log.*

# Or delete all logs
rm storage/logs/supervisor/route-watcher-dev*.log*

# Supervisor will recreate on next write
```

**Adjust Log Rotation:**
```bash
# Edit config to rotate sooner
sudo nano /etc/supervisor/conf.d/laravel-route-watcher-prod.conf

# Change stdout_logfile_maxbytes value (in bytes)
# 1MB = 1000000
# 5MB = 5000000
# 10MB = 10000000

# Reload
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-route-watcher-prod
```

---

## Part 6: Maintenance Tasks

### Daily Operations

```bash
# Check everything is running
sudo supervisorctl status

# Monitor logs
tail -20 storage/logs/supervisor/route-watcher-prod.log

# Verify routes are being synced
php artisan tinker
>>> AppRoute::count()
>>> AppRoute::where('updated_at', '>', now()->subHour())->count()
```

### Weekly Maintenance

```bash
# Review error logs for patterns
tail -100 storage/logs/supervisor/route-watcher-prod-error.log

# Check routes match current files
php artisan routes:sync

# Verify all permissions are still correct
sudo supervisorctl status laravel-route-watcher-dev
sudo supervisorctl status laravel-route-watcher-prod
```

### Monthly Tasks

```bash
# Archive old logs
tar -czf storage/logs/supervisor/route-watcher-$(date +%Y-%m).tar.gz \
    storage/logs/supervisor/route-watcher-*.log.*

# Clear archived logs older than 3 months
find storage/logs/supervisor -name "route-watcher-*.tar.gz" -mtime +90 -delete

# Review overall statistics
php artisan tinker
>>> use App\Services\RouteSyncService;
>>> (new RouteSyncService())->getStatistics()
```

### Upgrading Supervisor Configuration

If you need to update the configuration files:

```bash
# 1. Backup current config
sudo cp /etc/supervisor/conf.d/laravel-route-watcher-dev.conf \
        /etc/supervisor/conf.d/laravel-route-watcher-dev.conf.bak

# 2. Copy new config
sudo cp config/supervisor/laravel-route-watcher-dev.conf \
        /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# 3. Substitute paths
LARAVEL_ROOT=$(pwd)
sudo sed -i "s|%(ENV_LARAVEL_ROOT)s|${LARAVEL_ROOT}|g" \
    /etc/supervisor/conf.d/laravel-route-watcher-dev.conf

# 4. Reload
sudo supervisorctl reread
sudo supervisorctl update

# 5. Restart service
sudo supervisorctl restart laravel-route-watcher-dev

# 6. Verify
sudo supervisorctl status laravel-route-watcher-dev
```

---

## Part 7: Environment-Specific Setups

### macOS (Development)

```bash
# Install supervisor
brew install supervisor

# Verify installation
supervisord --version

# Run setup script
sudo ./scripts/setup-supervisor.sh dev

# Start supervisor
brew services start supervisor

# View supervisor programs
supervisorctl status

# Stop supervisor
brew services stop supervisor
```

### Ubuntu/Debian (Production)

```bash
# Install supervisor
sudo apt-get update
sudo apt-get install supervisor

# Verify installation
supervisord --version

# Run setup script
sudo ./scripts/setup-supervisor.sh prod

# Supervisor starts automatically on boot

# Check status
sudo systemctl status supervisor

# Manually restart if needed
sudo systemctl restart supervisor
```

### CentOS/RHEL (Production)

```bash
# Install supervisor
sudo yum install supervisor

# Verify installation
supervisord --version

# Run setup script
sudo ./scripts/setup-supervisor.sh prod

# Enable on boot and start
sudo systemctl enable supervisord
sudo systemctl start supervisord

# Check status
sudo systemctl status supervisord
```

---

## Summary

**Key Points to Remember:**

1. ✅ Use `sudo ./scripts/setup-supervisor.sh` for initial setup
2. ✅ Check status with `sudo supervisorctl status`
3. ✅ Monitor logs with `tail -f storage/logs/supervisor/route-watcher-*.log`
4. ✅ Reload after config changes: `sudo supervisorctl reread && sudo supervisorctl update`
5. ✅ Development uses shorter interval (3s), production uses longer (15s)
6. ✅ Logs auto-rotate based on max file size
7. ✅ Service auto-restarts on crash
8. ✅ Check supervisor logs if service won't start: `sudo tail -f /var/log/supervisor/supervisord.log`

**Common Command Sequence:**

```bash
# Setup (one-time)
sudo ./scripts/setup-supervisor.sh dev

# Monitor (ongoing)
sudo supervisorctl status
tail -f storage/logs/supervisor/route-watcher-dev.log

# Troubleshoot (if needed)
sudo supervisorctl restart laravel-route-watcher-dev
sudo tail -f /var/log/supervisor/supervisord.log
```
