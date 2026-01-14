# Advanced Supervisor Configuration for Document Module

This guide covers advanced topics for customizing and extending the Supervisor configuration.

## 📊 Performance Tuning

### Determining Optimal Number of Workers

The number of worker processes depends on your email volume and server resources.

```conf
; Low volume (< 100 emails/day)
numprocs=1

; Medium volume (100-1000 emails/day)
numprocs=2

; High volume (1000-5000 emails/day)
numprocs=4

; Very high volume (> 5000 emails/day)
numprocs=8
```

### Increase Worker Memory Limit

If workers are crashing with out-of-memory errors:

```conf
; Before the command line
; Increase PHP memory limit
command=/usr/bin/php -d memory_limit=512M /var/www/manager/artisan queue:work ...
```

### Adjust Timeout for Large Emails

If you're sending emails with attachments:

```conf
; Increase timeout from 60 to 300 seconds
command=/usr/bin/php /var/www/manager/artisan queue:work --queue=emails --timeout=300 ...
```

### Fine-tune Max Jobs and Time

```conf
; Process up to 5000 jobs before restart
--max-jobs=5000

; Restart worker every 2 hours (7200 seconds)
--max-time=7200
```

## 🔄 Multiple Queue Handlers

If you need to process different queues separately:

### Create a Second Configuration

Create `/opt/homebrew/etc/supervisor.d/document-queue-priority.conf`:

```conf
[program:document-queue-priority]
process_name=%(program_name)s_%(process_num)d
command=/usr/bin/php /var/www/manager/artisan queue:work --queue=priority --tries=5
autostart=true
autorestart=true
user=www-data
numprocs=2
stdout_logfile=/var/log/supervisor/document-queue-priority.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=10
stderr_logfile=/var/log/supervisor/document-queue-priority-errors.log
```

Then reload:
```bash
sudo supervisorctl reread && sudo supervisorctl update
```

## 🎯 Event Listeners

### Auto-restart on Memory Leak

Create `/opt/homebrew/etc/supervisor.d/eventlisteners.conf`:

```conf
[eventlistener:memory_monitor]
command=memmon -a 512000000 -m /opt/homebrew/bin/supervisorctl
events=TICK_60
buffer_size=100
```

This restarts workers if they exceed 512MB.

### Email Notifications on Failure

```conf
[eventlistener:failure_email]
command=/path/to/failure_email.py
events=PROCESS_STATE,TICK_3600
buffer_size=100
```

## 🔐 Security Hardening

### Run Workers with Minimal Permissions

Instead of `www-data`, create a dedicated user:

```bash
# macOS
dseditgroup -o create -q _document_worker

# Linux
sudo useradd -r -s /bin/false _document_worker
```

Then use in config:
```conf
user=_document_worker
group=_document_worker
```

### Restrict Log File Permissions

```bash
# macOS
chmod 600 ~/.supervisor/logs/document-queue-emails.log

# Linux
sudo chmod 600 /var/log/supervisor/document-queue-emails.log
```

## 📈 Monitoring and Alerts

### Parse Logs for Metrics

Create a simple monitoring script:

```bash
#!/bin/bash

LOG_FILE="/var/log/supervisor/document-queue-emails.log"

# Count failed jobs in last hour
FAILED=$(grep "failed\|error" "$LOG_FILE" | tail -10 | wc -l)

if [ $FAILED -gt 5 ]; then
    # Send alert (email, Slack, etc.)
    echo "Alert: $FAILED failures in document queue"
fi
```

### Set Up with Cron

```bash
# Add to crontab
* * * * * /path/to/check-queue.sh
```

## 🔍 Debugging

### Enable Extended Logging

Modify the command to add verbose output:

```conf
command=/usr/bin/php /var/www/manager/artisan queue:work \
    --queue=emails \
    --verbose \
    --tries=3 \
    --timeout=60
```

### Log to Database Instead of Files

Create a custom logging listener that stores in DB:

```php
// app/Listeners/LogQueueActivity.php
namespace App\Listeners;

class LogQueueActivity
{
    public function handle($event)
    {
        QueueLog::create([
            'queue' => $event->queue,
            'job_id' => $event->job->getJobId(),
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }
}
```

## 🚀 High Availability Setup

### Supervisor Configuration for Load Balancing

Run supervisor on multiple servers and rotate:

```conf
; Server 1 - High priority queue
[program:document-queue-emails-high]
command=/usr/bin/php /var/www/manager/artisan queue:work --queue=emails-high --tries=5
numprocs=2

; Server 2 - Standard priority queue
[program:document-queue-emails-standard]
command=/usr/bin/php /var/www/manager/artisan queue:work --queue=emails-standard --tries=3
numprocs=4
```

### Health Check Script

```bash
#!/bin/bash

WORKERS=$(supervisorctl status document-queue-emails:* | grep RUNNING | wc -l)

if [ $WORKERS -eq 0 ]; then
    # Restart failed workers
    supervisorctl restart document-queue-emails:*

    # Alert
    mail -s "Alert: Document Queue Workers Down" admin@example.com
fi
```

## 📊 Integration with Monitoring Tools

### Prometheus Metrics

Create a metrics exporter:

```php
// routes/metrics.php
Route::get('/metrics/queue', function () {
    $pending = DB::table('jobs')->where('queue', 'emails')->count();
    $failed = DB::table('failed_jobs')->count();

    return response(
        "document_queue_pending $pending\n" .
        "document_queue_failed $failed\n"
    )->header('Content-Type', 'text/plain');
});
```

### Grafana Dashboard

Import queue metrics to Grafana for visualization:

```json
{
  "dashboard": {
    "title": "Document Queue Metrics",
    "panels": [
      {
        "title": "Pending Jobs",
        "targets": [
          {"expr": "document_queue_pending"}
        ]
      },
      {
        "title": "Failed Jobs",
        "targets": [
          {"expr": "document_queue_failed"}
        ]
      }
    ]
  }
}
```

## 🔧 Custom Process Management

### Graceful Shutdown Handler

```php
// app/Console/Commands/QueueWorkerGraceful.php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class QueueWorkerGraceful extends Command
{
    public function handle()
    {
        // Save state before shutdown
        $pendingCount = DB::table('jobs')->count();

        Log::info("Worker shutting down with $pendingCount pending jobs");

        // Call parent queue:work command
        $this->call('queue:work', [
            '--queue' => 'emails',
            '--tries' => 3,
            '--timeout' => 60,
        ]);
    }
}
```

## 📝 Troubleshooting Advanced Issues

### Workers Keep Crashing

Check the error log for patterns:
```bash
grep "Throwable\|Exception\|Fatal" /var/log/supervisor/document-queue-emails-errors.log
```

### Jobs Stay Pending Forever

Check if worker is actually running:
```bash
ps aux | grep "queue:work"
```

Monitor database queue:
```bash
# In Laravel Tinker
DB::table('jobs')->where('queue', 'emails')->count()
```

### High CPU Usage

Reduce workers or increase `--max-time`:
```conf
numprocs=2
command=... --max-time=3600
```

### Database Connection Errors

Add connection retry logic:
```conf
command=/usr/bin/php /var/www/manager/artisan queue:work \
    --queue=emails \
    --tries=5 \
    --delay=5
```

## 🎓 Best Practices

1. **Monitor Regularly**: Check logs at least daily
2. **Test Updates**: Always test config changes on staging
3. **Document Changes**: Keep notes on what you modified and why
4. **Backup Config**: Version control your supervisor configs
5. **Plan Scaling**: Know when you'll need more workers
6. **Alert Setup**: Configure notifications for failures
7. **Log Rotation**: Ensure logs don't fill disk space
8. **Regular Reviews**: Check performance monthly

## 📚 Additional Resources

- [Supervisor Documentation](https://supervisord.readthedocs.io/)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Job Middleware](https://laravel.com/docs/queues#middleware)
- [Handling Timeouts](https://laravel.com/docs/queues#dealing-with-failed-jobs)
