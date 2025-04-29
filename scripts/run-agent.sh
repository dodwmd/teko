#!/bin/bash
set -e

# If we're in Docker, use the system Python
if [ -f /.dockerenv ]; then
    # Check if virtual environment exists in Docker
    if [ -d "/var/www/agents/.venv" ]; then
        echo "Using Docker virtual environment"
        PYTHON="/var/www/agents/.venv/bin/python"
    else
        echo "Using system Python in Docker"
        PYTHON="python3"
    fi
else
    # Check if we have a virtual environment
    if [ -d "./agents/.venv" ]; then
        echo "Using local virtual environment"
        PYTHON="./agents/.venv/bin/python"
    else
        echo "Virtual environment not found, please run 'make setup-python-env'"
        exit 1
    fi
fi

# Check if we have an agent name
if [ -z "$1" ]; then
    echo "Usage: $0 <agent_name>"
    exit 1
fi

AGENT_NAME=$1
shift

# Run the agent
cd agents
$PYTHON -m agents.$AGENT_NAME "$@"
