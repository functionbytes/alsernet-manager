#!/bin/bash

# Document Module Supervisor Utilities
# Helpful scripts for common supervisor operations

# ============================================================================
# CONFIGURATION
# ============================================================================

# Detect OS
if [[ "$OSTYPE" == "darwin"* ]]; then
    OS="mac"
    LOG_FILE="$HOME/.supervisor/logs/document-queue-emails.log"
    ERROR_LOG="$HOME/.supervisor/logs/document-queue-errors.log"
elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
    OS="linux"
    LOG_FILE="/var/log/supervisor/document-queue-emails.log"
    ERROR_LOG="/var/log/supervisor/document-queue-emails-errors.log"
else
    echo "Unsupported OS"
    exit 1
fi

SUDO=""
[[ "$OS" == "linux" ]] && SUDO="sudo"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# ============================================================================
# FUNCTIONS
# ============================================================================

status() {
    echo -e "${BLUE}Document Queue Worker Status${NC}\n"
    $SUDO supervisorctl status document-queue-emails:* || exit 1
}

logs() {
    echo -e "${BLUE}Tailing Document Queue Logs (Ctrl+C to exit)${NC}\n"
    if [[ "$OS" == "mac" ]]; then
        tail -f "$LOG_FILE"
    else
        $SUDO tail -f "$LOG_FILE"
    fi
}

errors() {
    echo -e "${BLUE}Tailing Document Queue Error Logs (Ctrl+C to exit)${NC}\n"
    if [[ "$OS" == "mac" ]]; then
        tail -f "$ERROR_LOG"
    else
        $SUDO tail -f "$ERROR_LOG"
    fi
}

restart() {
    echo -e "${YELLOW}Restarting document queue workers...${NC}"
    $SUDO supervisorctl restart document-queue-emails:* || exit 1
    echo -e "${GREEN}✓ Workers restarted${NC}"
    sleep 2
    status
}

stop() {
    echo -e "${YELLOW}Stopping document queue workers...${NC}"
    $SUDO supervisorctl stop document-queue-emails:* || exit 1
    echo -e "${GREEN}✓ Workers stopped${NC}"
}

start() {
    echo -e "${YELLOW}Starting document queue workers...${NC}"
    $SUDO supervisorctl start document-queue-emails:* || exit 1
    echo -e "${GREEN}✓ Workers started${NC}"
    sleep 2
    status
}

reload() {
    echo -e "${YELLOW}Reloading supervisor configuration...${NC}"
    $SUDO supervisorctl reread || exit 1
    echo -e "${GREEN}✓ Configuration read${NC}"
    $SUDO supervisorctl update || exit 1
    echo -e "${GREEN}✓ Configuration updated${NC}"
    sleep 2
    status
}

stats() {
    echo -e "${BLUE}Document Queue Statistics${NC}\n"

    if [[ "$OS" == "mac" ]]; then
        LOG="$LOG_FILE"
    else
        LOG="/var/log/supervisor/document-queue-emails.log"
    fi

    echo "Total jobs processed:"
    grep -c "processed" "$LOG" 2>/dev/null || echo "0"

    echo ""
    echo "Jobs failed:"
    grep -c "failed\|error" "$LOG" 2>/dev/null || echo "0"

    echo ""
    echo "Latest entries:"
    if [[ "$OS" == "mac" ]]; then
        tail -5 "$LOG"
    else
        $SUDO tail -5 "$LOG"
    fi
}

queue_info() {
    echo -e "${BLUE}Queue Information${NC}\n"
    echo "Queue driver: database (MySQL)"
    echo "Queue table: jobs"
    echo "Queue name: emails"
    echo ""

    # Try to show pending jobs count
    echo "Pending jobs in queue:"
    php artisan tinker --execute='
    try {
        $count = DB::table("jobs")->where("queue", "emails")->count();
        echo "  Total: $count jobs\n";
        $failed = DB::table("failed_jobs")->count();
        echo "  Failed: $failed jobs\n";
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    ' 2>/dev/null || echo "  Could not determine job count"
}

uptime() {
    echo -e "${BLUE}Worker Uptime${NC}\n"
    $SUDO supervisorctl status document-queue-emails:* | grep -oP 'uptime \K[^,]*'
}

clean_logs() {
    echo -e "${YELLOW}Cleaning old log files...${NC}"

    if [[ "$OS" == "mac" ]]; then
        LOGS_DIR="$HOME/.supervisor/logs"
    else
        LOGS_DIR="/var/log/supervisor"
    fi

    find "$LOGS_DIR" -name "document-queue-*.log*" -type f -mtime +30 -delete
    echo -e "${GREEN}✓ Old logs removed${NC}"
}

benchmark() {
    echo -e "${BLUE}Document Queue Performance Benchmark${NC}\n"

    if [[ "$OS" == "mac" ]]; then
        LOG="$LOG_FILE"
    else
        LOG="/var/log/supervisor/document-queue-emails.log"
    fi

    echo "Jobs per hour (last hour):"
    NOW=$(date +%s)
    HOUR_AGO=$((NOW - 3600))

    # Very basic - just show last entries
    tail -20 "$LOG" 2>/dev/null || echo "Unable to read logs"
}

config() {
    echo -e "${BLUE}Configuration Location${NC}\n"

    if [[ "$OS" == "mac" ]]; then
        CONFIG="/opt/homebrew/etc/supervisor.d/document-queue.conf"
    else
        CONFIG="/etc/supervisor/conf.d/document-queue.conf"
    fi

    echo "Config file: $CONFIG"
    echo ""
    echo "To edit:"
    if [[ "$OS" == "mac" ]]; then
        echo "  sudo nano $CONFIG"
    else
        echo "  sudo nano $CONFIG"
    fi
    echo ""
    echo "Then reload:"
    echo "  supervisorctl reread && supervisorctl update"
}

help() {
    cat << EOF
${BLUE}Document Module Supervisor Utilities${NC}

Usage: ./utils.sh [command]

Commands:
  ${YELLOW}status${NC}        - Show current worker status
  ${YELLOW}logs${NC}          - Tail real-time logs
  ${YELLOW}errors${NC}        - Tail error logs
  ${YELLOW}restart${NC}       - Restart workers
  ${YELLOW}stop${NC}          - Stop workers
  ${YELLOW}start${NC}         - Start workers
  ${YELLOW}reload${NC}        - Reload supervisor configuration
  ${YELLOW}stats${NC}         - Show queue statistics
  ${YELLOW}queue${NC}         - Show pending jobs information
  ${YELLOW}uptime${NC}        - Show worker uptime
  ${YELLOW}clean${NC}         - Clean old log files
  ${YELLOW}benchmark${NC}     - Show performance benchmark
  ${YELLOW}config${NC}        - Show configuration location
  ${YELLOW}help${NC}          - Show this help message

Examples:
  ./utils.sh status          # Check if workers are running
  ./utils.sh logs            # Monitor logs in real-time
  ./utils.sh restart         # Restart all workers
  ./utils.sh queue           # Check pending jobs
  ./utils.sh stats           # View statistics

EOF
}

# ============================================================================
# MAIN
# ============================================================================

case "${1:-help}" in
    status)
        status
        ;;
    logs)
        logs
        ;;
    errors)
        errors
        ;;
    restart)
        restart
        ;;
    stop)
        stop
        ;;
    start)
        start
        ;;
    reload)
        reload
        ;;
    stats)
        stats
        ;;
    queue)
        queue_info
        ;;
    uptime)
        uptime
        ;;
    clean)
        clean_logs
        ;;
    benchmark)
        benchmark
        ;;
    config)
        config
        ;;
    help|--help|-h)
        help
        ;;
    *)
        echo "Unknown command: $1"
        help
        exit 1
        ;;
esac
