#!/bin/bash
echo "============================================"
echo " Palestine Creative Hub - Dusk Test Runner"
echo "============================================"
echo ""

# Check if ChromeDriver is running
if ! pgrep -x "chromedriver" > /dev/null; then
    echo "Starting ChromeDriver..."
    chromedriver --port=9515 &
    sleep 2
else
    echo "ChromeDriver is already running."
fi

echo ""
echo "Running Dusk Tests..."
echo ""

# Run all tests or a specific test file
if [ -z "$1" ]; then
    php artisan dusk --env=dusk.production
else
    php artisan dusk --env=dusk.production --filter="$1"
fi

echo ""
echo "============================================"
echo " Tests Complete! Screenshots in:"
echo " tests/Browser/screenshots/"
echo "============================================"
