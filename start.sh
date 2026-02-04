#!/bin/bash
# Start PHP-FPM in background
php-fpm -D

# Wait for PHP-FPM socket to be created
while [ ! -S /run/php/php8.3-fpm.sock ]; do
  echo "Waiting for PHP-FPM socket..."
  sleep 1
done

echo "PHP-FPM socket ready, starting nginx..."

# Start nginx in foreground
nginx -g 'daemon off;'
