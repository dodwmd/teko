#!/bin/bash
set -e

# Run Python tests
echo "Running Python tests..."

# Check if Python is installed and has the required modules
if command -v python3 &> /dev/null; then
    # Try to run pytest
    if python3 -c "import pytest" &> /dev/null; then
        echo "Running pytest for Python files..."
        cd agents && python3 -m pytest --cov=. --cov-report=term
    else
        echo "Warning: pytest not installed. To install, run: pip install pytest pytest-cov"
    fi
else
    echo "Error: Python 3 is not installed or not in PATH"
    exit 1
fi
