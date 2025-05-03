#!/bin/bash
set -e

# Function to clean up background processes
cleanup() {
    echo "Cleaning up background processes..."
    if [ ! -z "$CHROME_DRIVER_PID" ]; then
        echo "Stopping Chrome Driver..."
        kill $CHROME_DRIVER_PID 2>/dev/null || true
    fi
    
    if [ ! -z "$LARAVEL_SERVER_PID" ]; then
        echo "Stopping Laravel server..."
        kill $LARAVEL_SERVER_PID 2>/dev/null || true
    fi
    
    exit 0
}

# Trap signals to ensure clean cleanup on exit, interrupt, or termination
trap cleanup EXIT INT TERM

# Function to check if a port is in use
port_in_use() {
    # Convert decimal port to hex (for /proc/net/tcp format)
    local hex_port=$(printf "%04X" "$1")
    
    # Check if port is in use by checking /proc/net/tcp
    grep -q ":${hex_port}" /proc/net/tcp /proc/net/tcp6 2>/dev/null
    return $?
}

# If ChromeDriver is already running, kill it to ensure a clean state
if port_in_use 9515; then
    echo "ChromeDriver is already running on port 9515, killing existing process..."
    pkill -f "chromedriver" || true
    sleep 2
fi

# Install Chrome Driver if needed
if [ "$CI" = "true" ]; then
    # On CI environment, use specific Chrome driver
    php artisan dusk:chrome-driver --detect
else
    # On local environment, check if driver exists
    if [ ! -f "./vendor/laravel/dusk/bin/chromedriver-linux" ]; then
        echo "Installing ChromeDriver..."
        php artisan dusk:chrome-driver
    fi
fi

# Start Chrome Driver in background with more visibility
echo "Starting Chrome Driver on port 9515..."
./vendor/laravel/dusk/bin/chromedriver-linux --port=9515 --verbose > /tmp/chromedriver.log 2>&1 &
CHROME_DRIVER_PID=$!

# Wait for ChromeDriver to be ready - check if process is alive and port is open
echo "Waiting for ChromeDriver to be ready on port 9515..."
sleep 3
max_retries=5
retry_count=0
while ! port_in_use 9515; do
    retry_count=$((retry_count+1))
    if [ $retry_count -gt $max_retries ]; then
        echo "ERROR: ChromeDriver failed to start properly on port 9515 after $max_retries attempts"
        echo "Last few lines of ChromeDriver log:"
        tail -n 20 /tmp/chromedriver.log
        exit 1
    fi
    
    echo "ChromeDriver not ready yet, checking if process is alive..."
    if ! kill -0 $CHROME_DRIVER_PID 2>/dev/null; then
        echo "ERROR: ChromeDriver process died. Check /tmp/chromedriver.log for details"
        tail -n 20 /tmp/chromedriver.log
        exit 1
    fi
    
    echo "Waiting for ChromeDriver to be ready (attempt $retry_count of $max_retries)..."
    sleep 3
done

echo "ChromeDriver started successfully on port 9515!"

# Check if Laravel is already running on port 8000
if port_in_use 8000; then
    echo "Laravel server is already running on port 8000, using existing server..."
    LARAVEL_SERVER_PID=""
else
    # Start Laravel server in background
    echo "Starting Laravel server on port 8000..."
    php artisan serve --port=8000 > /tmp/laravel-server.log 2>&1 &
    LARAVEL_SERVER_PID=$!
    
    # Give processes a moment to start
    echo "Waiting for Laravel server to be ready..."
    sleep 5
fi

# Ensure environment variables are loaded
export DUSK_DRIVER_URL=http://localhost:9515

# Run Dusk tests
echo "Running Dusk tests now..."
PHP_CLI_SERVER_WORKERS=5 php artisan dusk

# Cleanup happens automatically thanks to trap
