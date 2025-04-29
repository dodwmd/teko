#!/bin/bash
set -e

# Lint Python files with Black and isort
echo "Running Python linting tools..."

# Store the current directory
CURRENT_DIR=$(pwd)

# Check if Python is installed and has the required modules
if command -v python3 &> /dev/null; then
    # Check if agents directory exists
    if [ ! -d "agents" ]; then
        echo "Error: agents directory not found"
        exit 1
    fi
    
    # Try to run Black
    if python3 -c "import black" &> /dev/null; then
        echo "Running Black for Python files..."
        python3 -m black agents
    else
        echo "Warning: Black not installed. To install, run: pip install black"
    fi
    
    # Try to run isort
    if python3 -c "import isort" &> /dev/null; then
        echo "Running isort for Python files..."
        python3 -m isort agents
    else
        echo "Warning: isort not installed. To install, run: pip install isort"
    fi
else
    echo "Error: Python 3 is not installed or not in PATH"
    exit 1
fi
