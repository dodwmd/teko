#!/bin/bash
set -e

# Lint PHP files with Pint
echo "Running Laravel Pint for PHP files..."
php ./vendor/bin/pint

# Lint Python files with Black and iSort if available
if [ -d "agents" ]; then
    echo "Checking Python linting tools..."
    
    # Check if Python is installed and has the required modules
    if command -v python3 &> /dev/null; then
        # Try to run Black
        if python3 -c "import black" &> /dev/null; then
            echo "Running Black for Python files..."
            cd agents && python3 -m black .
            cd $OLDPWD  # Return to the original directory
        else
            echo "Warning: Black not installed. To install, run: pip install -r agents/requirements.txt"
        fi
        
        # Try to run isort
        if python3 -c "import isort" &> /dev/null; then
            echo "Running iSort for Python files..."
            cd agents && python3 -m isort .
            cd $OLDPWD  # Return to the original directory
        else
            echo "Warning: isort not installed. To install, run: pip install -r agents/requirements.txt"
        fi
    else
        echo "Warning: Python 3 not found. Python linting skipped."
    fi
fi

echo "Linting completed successfully!"
