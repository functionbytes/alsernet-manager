#!/bin/bash

# Document Module Supervisor Installation Script
# This script automates the installation and configuration of Supervisor
# for the Document Module queue worker on macOS and Linux

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ============================================================================
# FUNCTIONS
# ============================================================================

print_header() {
    echo -e "\n${BLUE}▶ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

confirm() {
    local prompt="$1"
    local response

    read -p "$(echo -e ${YELLOW}$prompt${NC}) (y/n): " response

    case "$response" in
        [yY][eE][sS]|[yY])
            return 0
            ;;
        *)
            return 1
            ;;
    esac
}

detect_os() {
    if [[ "$OSTYPE" == "darwin"* ]]; then
        OS="mac"
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        OS="linux"
    else
        print_error "Unsupported operating system: $OSTYPE"
        exit 1
    fi
}

check_supervisor() {
    if [[ "$OS" == "mac" ]]; then
        if ! command -v supervisord &> /dev/null; then
            print_error "Supervisor is not installed on macOS"
            echo "Install it with: brew install supervisor"
            return 1
        fi
    elif [[ "$OS" == "linux" ]]; then
        if ! command -v supervisord &> /dev/null; then
            print_error "Supervisor is not installed on Linux"
            echo "Install it with: sudo apt-get install supervisor"
            return 1
        fi
    fi
    return 0
}

check_php() {
    if [[ "$OS" == "mac" ]]; then
        if [[ ! -f "/opt/homebrew/opt/php@8.4/bin/php" ]]; then
            print_warning "PHP 8.4 not found at /opt/homebrew/opt/php@8.4/bin/php"
            echo "Detected PHP at:"
            which php
            read -p "Use this PHP? (y/n): " response
            if [[ ! "$response" =~ ^[yY]$ ]]; then
                return 1
            fi
        fi
    elif [[ "$OS" == "linux" ]]; then
        if [[ ! -f "/usr/bin/php" ]]; then
            print_warning "PHP not found at /usr/bin/php"
            echo "Detected PHP at:"
            which php
            read -p "Use this PHP? (y/n): " response
            if [[ ! "$response" =~ ^[yY]$ ]]; then
                return 1
            fi
        fi
    fi
    return 0
}

install_mac() {
    print_header "Installing Supervisor Configuration for macOS"

    # Create log directory
    print_header "Creating log directory..."
    mkdir -p ~/.supervisor/logs
    print_success "Log directory created at ~/.supervisor/logs"

    # Copy configuration
    print_header "Copying configuration file..."
    CONFIG_FILE="/opt/homebrew/etc/supervisor.d/document-queue.conf"

    if sudo cp "$(dirname "$0")/mac/document-queue.conf" "$CONFIG_FILE"; then
        print_success "Configuration copied to $CONFIG_FILE"
    else
        print_error "Failed to copy configuration"
        return 1
    fi

    # Fix permissions
    sudo chown root:wheel "$CONFIG_FILE"
    sudo chmod 644 "$CONFIG_FILE"

    # Start or restart Supervisor
    print_header "Starting Supervisor..."
    if brew services start supervisor 2>/dev/null; then
        print_success "Supervisor started successfully"
    elif brew services restart supervisor 2>/dev/null; then
        print_success "Supervisor restarted successfully"
    else
        print_warning "Could not start Supervisor via Homebrew services"
        print_warning "Try starting manually: supervisord"
    fi

    # Give it a moment to start
    sleep 2

    # Reload configuration
    print_header "Loading configuration..."
    if supervisorctl reread; then
        print_success "Configuration read"
    else
        print_error "Failed to read configuration"
        return 1
    fi

    if supervisorctl update; then
        print_success "Configuration updated"
    else
        print_error "Failed to update configuration"
        return 1
    fi

    # Show status
    print_header "Process Status"
    supervisorctl status document-queue-emails:*

    print_success "Installation complete for macOS!"
}

install_linux() {
    print_header "Installing Supervisor Configuration for Linux"

    # Create log directory
    print_header "Creating log directory..."
    if sudo mkdir -p /var/log/supervisor; then
        sudo chown www-data:www-data /var/log/supervisor
        sudo chmod 755 /var/log/supervisor
        print_success "Log directory created at /var/log/supervisor"
    else
        print_error "Failed to create log directory"
        return 1
    fi

    # Copy configuration
    print_header "Copying configuration file..."
    CONFIG_FILE="/etc/supervisor/conf.d/document-queue.conf"

    if sudo cp "$(dirname "$0")/linux/document-queue.conf" "$CONFIG_FILE"; then
        print_success "Configuration copied to $CONFIG_FILE"
    else
        print_error "Failed to copy configuration"
        return 1
    fi

    # Fix permissions
    sudo chown root:root "$CONFIG_FILE"
    sudo chmod 644 "$CONFIG_FILE"

    # Restart Supervisor
    print_header "Restarting Supervisor..."
    if sudo systemctl restart supervisor; then
        print_success "Supervisor restarted successfully"
    else
        print_error "Failed to restart Supervisor"
        print_warning "Try manually: sudo systemctl restart supervisor"
    fi

    # Give it a moment to start
    sleep 2

    # Reload configuration
    print_header "Loading configuration..."
    if sudo supervisorctl reread; then
        print_success "Configuration read"
    else
        print_error "Failed to read configuration"
        return 1
    fi

    if sudo supervisorctl update; then
        print_success "Configuration updated"
    else
        print_error "Failed to update configuration"
        return 1
    fi

    # Show status
    print_header "Process Status"
    sudo supervisorctl status document-queue-emails:*

    # Enable autostart
    print_header "Enabling autostart on boot..."
    if sudo systemctl enable supervisor; then
        print_success "Supervisor enabled for autostart"
    fi

    print_success "Installation complete for Linux!"
}

show_next_steps() {
    echo -e "\n${BLUE}════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}✓ Supervisor is now configured and running!${NC}"
    echo -e "${BLUE}════════════════════════════════════════════════════════${NC}\n"

    if [[ "$OS" == "mac" ]]; then
        echo "Useful commands for macOS:"
        echo ""
        echo -e "  ${YELLOW}supervisorctl status${NC}                  # View all processes"
        echo -e "  ${YELLOW}supervisorctl status document-queue-emails:*${NC}  # View document queue"
        echo -e "  ${YELLOW}supervisorctl tail document-queue-emails${NC}   # View logs"
        echo -e "  ${YELLOW}supervisorctl restart document-queue-emails:*${NC} # Restart workers"
        echo -e "  ${YELLOW}supervisorctl stop document-queue-emails:*${NC}    # Stop workers"
        echo -e "  ${YELLOW}tail -f ~/.supervisor/logs/document-queue-emails.log${NC} # Real-time logs"
        echo ""
    elif [[ "$OS" == "linux" ]]; then
        echo "Useful commands for Linux:"
        echo ""
        echo -e "  ${YELLOW}sudo supervisorctl status${NC}                  # View all processes"
        echo -e "  ${YELLOW}sudo supervisorctl status document-queue-emails:*${NC}  # View document queue"
        echo -e "  ${YELLOW}sudo supervisorctl tail document-queue-emails${NC}   # View logs"
        echo -e "  ${YELLOW}sudo supervisorctl restart document-queue-emails:*${NC} # Restart workers"
        echo -e "  ${YELLOW}sudo supervisorctl stop document-queue-emails:*${NC}    # Stop workers"
        echo -e "  ${YELLOW}sudo tail -f /var/log/supervisor/document-queue-emails.log${NC} # Real-time logs"
        echo ""
    fi

    echo "Configuration file location:"
    if [[ "$OS" == "mac" ]]; then
        echo -e "  ${YELLOW}/opt/homebrew/etc/supervisor.d/document-queue.conf${NC}"
    elif [[ "$OS" == "linux" ]]; then
        echo -e "  ${YELLOW}/etc/supervisor/conf.d/document-queue.conf${NC}"
    fi
    echo ""

    echo "For detailed setup instructions, see: SETUP.md"
    echo ""
}

# ============================================================================
# MAIN SCRIPT
# ============================================================================

echo -e "${BLUE}"
cat << "EOF"
╔════════════════════════════════════════════════════════════════╗
║     Document Module Supervisor Installation                   ║
║     Queue Worker for Email Processing                         ║
╚════════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Detect OS
detect_os
print_success "Detected OS: $OS"

# Check prerequisites
print_header "Checking prerequisites..."

if ! check_supervisor; then
    print_error "Supervisor is required but not installed"
    exit 1
fi
print_success "Supervisor is installed"

if ! check_php; then
    print_error "PHP is required but not found"
    exit 1
fi
print_success "PHP is installed"

# Confirm before installation
echo ""
if ! confirm "Continue with installation on $OS?"; then
    print_warning "Installation cancelled"
    exit 0
fi

# Run installation
echo ""
if [[ "$OS" == "mac" ]]; then
    if ! install_mac; then
        print_error "Installation failed on macOS"
        exit 1
    fi
elif [[ "$OS" == "linux" ]]; then
    if ! install_linux; then
        print_error "Installation failed on Linux"
        exit 1
    fi
fi

# Show next steps
show_next_steps

echo -e "${GREEN}Installation script completed successfully!${NC}\n"
