#!/bin/bash
set -e

# PHP Tests
echo "Running PHP tests with PHPUnit..."
php artisan test

# Python Tests (if agents directory exists and dependencies installed)
if [ -d "agents" ]; then
    echo "Checking Python testing tools..."
    
    # Check if Python is installed and has the required modules
    if command -v python3 &> /dev/null; then
        # Try to run pytest
        if python3 -c "import pytest" &> /dev/null; then
            echo "Running Python tests with pytest..."
            cd agents && python3 -m pytest --cov=. --cov-report=term
        else
            echo "Warning: pytest not installed. To install, run: pip install -r agents/requirements.txt"
        fi
    else
        echo "Warning: Python 3 not found. Python tests skipped."
    fi
fi

echo "All tests completed successfully!"
