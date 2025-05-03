#!/bin/bash
set -e

# PHP Tests
echo "Running PHP tests with PHPUnit..."
php artisan test

echo "PHP unit tests completed successfully!"
