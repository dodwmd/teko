#!/bin/bash
set -e

VENV_DIR="agents/.venv"
REQUIREMENTS_FILE="agents/requirements.txt"

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "Error: Python 3 is not installed. Please install it first."
    exit 1
fi

# Create virtual environment if it doesn't exist
if [ ! -d "$VENV_DIR" ]; then
    echo "Creating virtual environment in $VENV_DIR..."
    python3 -m venv $VENV_DIR
    echo "Virtual environment created."
else
    echo "Virtual environment already exists in $VENV_DIR."
fi

# Activate virtual environment and install dependencies
echo "Activating virtual environment and installing dependencies..."
source $VENV_DIR/bin/activate

# Upgrade pip
echo "Upgrading pip..."
pip install --upgrade pip

# Install requirements
if [ -f "$REQUIREMENTS_FILE" ]; then
    echo "Installing dependencies from $REQUIREMENTS_FILE..."
    pip install -r $REQUIREMENTS_FILE
else
    echo "Error: Requirements file $REQUIREMENTS_FILE not found."
    exit 1
fi

echo "Python virtual environment is now set up and dependencies are installed."
echo "To activate the virtual environment manually, run:"
echo "source $VENV_DIR/bin/activate"
echo ""
echo "To deactivate the virtual environment, run:"
echo "deactivate"
