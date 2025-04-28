#!/bin/bash
set -e

VENV_DIR="agents/.venv"
AGENT_DIR="agents"

# Check if the virtual environment exists
if [ ! -d "$VENV_DIR" ]; then
    echo "Virtual environment not found. Setting up first..."
    ./scripts/setup-python-env.sh
fi

# Activate virtual environment
source $VENV_DIR/bin/activate

# Check for command line arguments
if [ $# -eq 0 ]; then
    echo "Error: Please specify an agent script to run."
    echo "Usage: $0 <agent_script> [arguments]"
    echo "Example: $0 core/orchestrator.py"
    exit 1
fi

AGENT_SCRIPT="$1"
shift

# Check if agent script exists
if [ ! -f "$AGENT_DIR/$AGENT_SCRIPT" ]; then
    echo "Error: Agent script '$AGENT_DIR/$AGENT_SCRIPT' not found."
    exit 1
fi

echo "Running agent: $AGENT_SCRIPT"
echo "=================================================="

# Run the agent script with any additional arguments
cd $AGENT_DIR
python3 $AGENT_SCRIPT "$@"

# Return to original directory
cd - > /dev/null

# Deactivate virtual environment (optional)
# deactivate
