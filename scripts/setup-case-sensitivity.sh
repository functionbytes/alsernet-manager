#!/bin/bash

# Setup Script - Configure Git Case-Sensitivity for Project

echo "🔧 Configuring Git Case-Sensitivity Settings..."
echo ""

# Get current directory
REPO_DIR=$(git rev-parse --show-toplevel)

# Check if we're in a git repository
if [ -z "$REPO_DIR" ]; then
    echo "❌ Error: Not in a Git repository!"
    exit 1
fi

# Set core.ignorecase to false
git config core.ignorecase false

# Verify the setting
IGNORE_CASE=$(git config core.ignorecase)

if [ "$IGNORE_CASE" = "false" ]; then
    echo "✅ Git case-sensitivity configured successfully!"
    echo ""
    echo "Settings:"
    echo "  • core.ignorecase = false"
    echo ""
    echo "This configuration ensures Git respects file/directory case,"
    echo "preventing synchronization issues across macOS, Linux, and Windows."
    echo ""
else
    echo "❌ Failed to configure case-sensitivity!"
    exit 1
fi

# Check for existing case-sensitivity issues
echo "🔍 Scanning for case-sensitivity issues..."
echo ""

# Find files with conflicting cases
CONFLICTS=$(git ls-files | awk '{print tolower($0)}' | sort | uniq -d)

if [ -z "$CONFLICTS" ]; then
    echo "✅ No case-sensitivity conflicts detected!"
else
    echo "⚠️  Case-sensitivity conflicts found:"
    echo "$CONFLICTS"
    echo ""
    echo "Run the following command to fix them:"
    echo "  git mv [conflicting-path] [lowercase-path]"
fi

echo ""
echo "📖 For more information, see:"
echo "  docs/development/case-sensitivity-guidelines.md"
echo ""
