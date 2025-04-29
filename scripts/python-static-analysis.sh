#!/bin/bash
set -e

# Run static analysis for Python files
echo "Running Python static analysis tools..."

# Check if Python is installed and has the required modules
if command -v python3 &> /dev/null; then
    # Try to run Flake8
    if python3 -c "import flake8" &> /dev/null; then
        echo "Running Flake8 for Python files..."
        cd agents && python3 -m flake8 .
    else
        echo "Warning: Flake8 not installed. To install, run: pip install flake8"
    fi
    
    # Try to run MyPy
    if python3 -c "import mypy" &> /dev/null; then
        echo "Running MyPy for Python files..."
        cd agents && python3 -m mypy .
    else
        echo "Warning: MyPy not installed. To install, run: pip install mypy"
    fi
else
    echo "Error: Python 3 is not installed or not in PATH"
    exit 1
fi
