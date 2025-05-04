#!/bin/bash
set -e

# Function to clean up background processes
cleanup() {
    echo "Cleaning up background processes..."
    if [ ! -z "$CHROME_DRIVER_PID" ]; then
        echo "Stopping Chrome Driver..."
        kill -9 $CHROME_DRIVER_PID 2>/dev/null || true
        sleep 0.5
    fi
    
    if [ ! -z "$LARAVEL_SERVER_PID" ]; then
        echo "Stopping Laravel server..."
        kill -9 $LARAVEL_SERVER_PID 2>/dev/null || true
        sleep 0.5
    fi
    
    pkill -9 -f "chromedriver" || true
    
    if port_in_use 9515; then
        echo "Forcefully clearing port 9515..."
        kill_process_using_port 9515
    fi
    
    exit 0
}

trap cleanup EXIT INT TERM

port_in_use() {
    local hex_port=$(printf "%04X" "$1")
    
    grep -q ":${hex_port}\|:$(echo $hex_port | tr '[:upper:]' '[:lower:]')" /proc/net/tcp /proc/net/tcp6 2>/dev/null
    return $?
}

wait_for_port() {
    local port=$1
    local timeout=${2:-10}  
    local start_time=$(date +%s)
    local end_time=$((start_time + timeout))
    
    while [ $(date +%s) -lt $end_time ]; do
        if port_in_use $port; then
            return 0  
        fi
        sleep 0.2
    done
    
    return 1  
}

kill_process_using_port() {
    local port=$1
    local hex_port=$(printf "%04X" "$port")
    
    echo "Force killing any chrome/chromedriver processes..."
    pkill -9 -f "chromedriver" 2>/dev/null || true
    pkill -9 -f "Xvfb" 2>/dev/null || true  
    pkill -9 -f "chrome" 2>/dev/null || true
    
    if port_in_use $port; then
        echo "Port $port still in use after killing chrome processes, trying more aggressive methods."
        
        if command -v lsof >/dev/null 2>&1; then
            echo "Using lsof to identify the process using port $port..."
            lsof -i :$port | grep -v PID | awk '{print $2}' | xargs -r kill -9 2>/dev/null || true
        fi
        
        sleep 1
        
        if port_in_use $port; then
            echo "WARNING: Failed to free port $port despite multiple approaches."
            echo "This might be a stale entry in /proc/net/tcp that doesn't reflect actual usage."
            echo "We'll proceed anyway but might encounter issues."
        fi
    fi
}

echo "Ensuring completely clean state before starting..."
pkill -9 -f "chromedriver" 2>/dev/null || true
pkill -9 -f "chrome" 2>/dev/null || true
pkill -9 -f "Xvfb" 2>/dev/null || true
sleep 1

if port_in_use 9515; then
    echo "Port 9515 seems to be in use, attempting to aggressively free it..."
    kill_process_using_port 9515
    
    sleep 1.5
    
    if port_in_use 9515; then
        echo "WARNING: Port 9515 still appears in /proc/net/tcp but no process owns it."
        echo "This might be a stale entry. We'll proceed but might have issues."
    fi
fi

if [ "$CI" = "true" ]; then
    php artisan dusk:chrome-driver --detect
else
    echo "Installing/Updating ChromeDriver..."
    php artisan dusk:chrome-driver --detect
fi

echo "Starting Chrome Driver on port 9515..."
./vendor/laravel/dusk/bin/chromedriver-linux --port=9515 --verbose > /tmp/chromedriver.log 2>&1 &
CHROME_DRIVER_PID=$!

echo "Waiting for ChromeDriver to be ready on port 9515..."
if wait_for_port 9515 5; then
    echo "ChromeDriver started successfully on port 9515!"
else
    if ! kill -0 $CHROME_DRIVER_PID 2>/dev/null; then
        echo "ERROR: ChromeDriver process died. Check /tmp/chromedriver.log for details"
        tail -n 20 /tmp/chromedriver.log
        exit 1
    fi
    
    echo "ChromeDriver not ready yet, trying again with longer timeout..."
    if wait_for_port 9515 10; then
        echo "ChromeDriver started successfully on port 9515!"
    else
        echo "ERROR: ChromeDriver failed to start properly on port 9515"
        echo "Last few lines of ChromeDriver log:"
        tail -n 20 /tmp/chromedriver.log
        exit 1
    fi
fi

if port_in_use 8000; then
    echo "Laravel server is already running on port 8000, using existing server..."
    LARAVEL_SERVER_PID=""
else
    echo "Starting Laravel server on port 8000..."
    php artisan serve --port=8000 > /tmp/laravel-server.log 2>&1 &
    LARAVEL_SERVER_PID=$!
    
    echo "Waiting for Laravel server to be ready..."
    if wait_for_port 8000 10; then
        echo "Laravel server is now running on port 8000"
    else
        echo "WARNING: Laravel server might not be fully ready, but we'll proceed"
    fi
fi

echo "Cleaning up any lingering Chrome sessions..."
rm -rf ~/.config/google-chrome/Default/Session* 2>/dev/null || true
rm -rf ~/.config/google-chrome/Default/Cookies* 2>/dev/null || true

export DUSK_DRIVER_URL=http://localhost:9515

echo "Running Dusk tests now..."
if [ $# -gt 0 ]; then
    PHP_CLI_SERVER_WORKERS=5 php artisan dusk --configuration=phpunit.dusk.xml "$@"
else
    PHP_CLI_SERVER_WORKERS=5 php artisan dusk --configuration=phpunit.dusk.xml
fi
