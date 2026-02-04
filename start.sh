#!/bin/bash
set -e

echo "==================================="
echo "Starting Laravel Application"
echo "PORT: $PORT"
echo "PWD: $(pwd)"
echo "==================================="

# Clear caches
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# Check if PORT is set
if [ -z "$PORT" ]; then
    echo "WARNING: PORT environment variable not set!"
    export PORT=8080
    echo "Using default PORT: 8080"
fi

echo "Starting PHP server on 0.0.0.0:$PORT from /app/public"

# Start PHP built-in server - MUST use $PORT variable
cd /app
exec php -S "0.0.0.0:${PORT}" -t public