#!/bin/bash
set -e

# Lint Python files with Black and isort
echo "Running Python linting tools..."

# Check if Python is installed and has the required modules
if command -v python3 &> /dev/null; then
    # Try to run Black
    if python3 -c "import black" &> /dev/null; then
        echo "Running Black for Python files..."
        cd agents && python3 -m black .
    else
        echo "Warning: Black not installed. To install, run: pip install black"
    fi
    
    # Try to run isort
    if python3 -c "import isort" &> /dev/null; then
        echo "Running isort for Python files..."
        cd agents && python3 -m isort .
    else
        echo "Warning: isort not installed. To install, run: pip install isort"
    fi
else
    echo "Error: Python 3 is not installed or not in PATH"
    exit 1
fi
