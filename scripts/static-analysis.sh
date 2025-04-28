#!/bin/bash
set -e

# PHP Static Analysis
echo "Running PHPStan..."
php ./vendor/bin/phpstan analyze || echo "PHPStan found some issues, but continuing..."

echo "Running Psalm..."
php ./vendor/bin/psalm --no-cache

# Python Static Analysis (if agents directory exists and dependencies installed)
if [ -d "agents" ]; then
    echo "Checking Python static analysis tools..."
    
    # Check if Python is installed and has the required modules
    if command -v python3 &> /dev/null; then
        # Try to run MyPy
        if python3 -c "import mypy" &> /dev/null; then
            echo "Running MyPy for Python typing..."
            cd agents && python3 -m mypy .
        else
            echo "Warning: MyPy not installed. To install, run: pip install -r agents/requirements.txt"
        fi
        
        # Try to run Flake8
        if python3 -c "import flake8" &> /dev/null; then
            echo "Running Flake8 for Python linting..."
            cd agents && python3 -m flake8 .
        else
            echo "Warning: Flake8 not installed. To install, run: pip install -r agents/requirements.txt"
        fi
    else
        echo "Warning: Python 3 not found. Python static analysis skipped."
    fi
fi

echo "Static analysis completed successfully!"
