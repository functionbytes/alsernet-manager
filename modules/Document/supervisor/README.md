# Document Module - Supervisor Configuration

Configuration files for running the Document Module queue worker using Supervisor.

## 📁 Structure

```
supervisor/
├── mac/                      # macOS configuration
│   └── document-queue.conf   # Supervisor config for macOS
├── linux/                    # Linux configuration
│   └── document-queue.conf   # Supervisor config for Linux
├── install.sh                # Automated installation script
├── SETUP.md                  # Detailed setup guide
└── README.md                 # This file
```

## ⚡ Quick Start

### Automated Installation (Recommended)

```bash
cd modules/Document/supervisor
./install.sh
```

The script will:
- ✅ Detect your operating system
- ✅ Check for Supervisor installation
- ✅ Copy the appropriate configuration
- ✅ Start the queue worker
- ✅ Show status

### Manual Installation

**macOS:**
```bash
brew install supervisor
mkdir -p ~/.supervisor/logs
sudo cp mac/document-queue.conf /opt/homebrew/etc/supervisor.d/
brew services start supervisor
supervisorctl update
```

**Linux:**
```bash
sudo apt-get install supervisor
sudo cp linux/document-queue.conf /etc/supervisor/conf.d/
sudo systemctl restart supervisor
sudo supervisorctl update
```

## ✅ Verify Installation

```bash
# macOS
supervisorctl status document-queue-emails:*

# Linux
sudo supervisorctl status document-queue-emails:*
```

You should see:
```
document-queue-emails:0  RUNNING  pid xxxx, uptime x:xx:xx
document-queue-emails:1  RUNNING  pid xxxx, uptime x:xx:xx
```

## 🔍 Common Commands

### View logs
```bash
# macOS
supervisorctl tail document-queue-emails

# Linux
sudo supervisorctl tail document-queue-emails
```

### Restart workers
```bash
# macOS
supervisorctl restart document-queue-emails:*

# Linux
sudo supervisorctl restart document-queue-emails:*
```

### Stop workers
```bash
# macOS
supervisorctl stop document-queue-emails:*

# Linux
sudo supervisorctl stop document-queue-emails:*
```

### Real-time monitoring
```bash
# macOS
tail -f ~/.supervisor/logs/document-queue-emails.log

# Linux
sudo tail -f /var/log/supervisor/document-queue-emails.log
```

## 📖 Documentation

For **detailed setup instructions, troubleshooting, and customization**, see [SETUP.md](./SETUP.md)

## 🛠️ What This Configures

The worker processes documents-related email jobs:
- Initial document requests
- Upload confirmations
- Approval/rejection notifications
- Custom email messages
- Document reminders

## 🔧 Configuration Details

| Parameter | Value | Purpose |
|-----------|-------|---------|
| `numprocs` | 2 | Number of worker processes |
| `--queue` | emails | Process jobs from 'emails' queue |
| `--tries` | 3 | Retry failed jobs 3 times |
| `--timeout` | 60 | Job timeout in seconds |
| `--max-jobs` | 1000 | Restart after processing 1000 jobs |
| `--max-time` | 3600 | Restart after 1 hour |

## 📝 Requirements

- **Supervisor:** Via Homebrew (macOS) or apt-get (Linux)
- **PHP:** 8.4+ (or your configured version)
- **Laravel Queue:** Database driver configured (✅ Already done)
- **Root/Sudo:** Required for configuration installation

## 🚀 Next Steps

1. Run the installation script
2. Verify the queue worker is running
3. Test by sending an email via the API
4. Monitor logs for issues

## ⚠️ Important Notes

- **Database Queue**: The queue driver is configured to use the database (MySQL) instead of Redis
- **Autostart**: Enabled by default - workers restart automatically if they fail
- **Logs**: Rotated automatically to prevent disk space issues
- **Performance**: Adjust `numprocs` based on email volume (2-4 is typical)

## 🐛 Troubleshooting

If you encounter issues, see [SETUP.md](./SETUP.md) for troubleshooting section.

Quick checks:
```bash
# Check if Supervisor is running
supervisord --version

# Check PHP version
php --version

# Verify configuration file is valid
sudo supervisorctl reread
```

## 📞 Support

For issues related to:
- **Queue setup**: See SETUP.md
- **Email delivery**: Check queue logs
- **Supervisor**: https://supervisord.readthedocs.io/
