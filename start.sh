#!/bin/bash
set -e

echo "Starting PHP built-in server on port $PORT..."

# Run Laravel optimizations
php artisan config:clear
php artisan cache:clear

# Start PHP server
exec php -S 0.0.0.0:$PORT -t /app/public /app/public/index.php