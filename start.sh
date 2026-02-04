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

# Start PHP built-in server with custom ini settings
cd /app
exec php -S "0.0.0.0:${PORT}" -t public \
    -d max_execution_time=300 \
    -d memory_limit=512M \
    -d upload_max_filesize=50M \
    -d post_max_size=50M \
    -d max_input_time=300