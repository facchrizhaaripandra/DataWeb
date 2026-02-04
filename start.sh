#!/bin/bash
# Start PHP-FPM in background
php-fpm -D

# Wait a moment for PHP-FPM to start
sleep 3

echo "Starting nginx..."

# Start nginx in foreground
nginx -g 'daemon off;'
