#!/bin/bash
set -e

# Run all test scripts in sequence
echo "=== RUNNING LINTING ==="
./scripts/lint.sh

echo ""
echo "=== RUNNING STATIC ANALYSIS ==="
./scripts/static-analysis.sh

echo ""
echo "=== RUNNING TESTS ==="
./scripts/run-tests.sh

echo ""
echo "All checks completed successfully!"
