#!/bin/bash
set -e

# Configure PHP-FPM to listen on TCP instead of socket
if [ -f /etc/php/8.3/fpm/pool.d/www.conf ]; then
    sed -i 's|listen = /run/php/php8.3-fpm.sock|listen = 127.0.0.1:9000|g' /etc/php/8.3/fpm/pool.d/www.conf
elif [ -f /usr/local/etc/php-fpm.d/www.conf ]; then
    sed -i 's|listen = .*|listen = 127.0.0.1:9000|g' /usr/local/etc/php-fpm.d/www.conf
fi

# Start PHP-FPM in background
echo "Starting PHP-FPM..."
php-fpm -D

# Wait for PHP-FPM to be ready
sleep 3

# Verify PHP-FPM is running
if ! pgrep -x php-fpm > /dev/null; then
    echo "ERROR: PHP-FPM failed to start"
    exit 1
fi

echo "PHP-FPM started successfully"

# Create nginx directory if not exists
mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled

# Create nginx config with correct PORT
echo "Creating Nginx configuration for port $PORT..."
cat > /etc/nginx/sites-available/default <<'EOF'
server {
    listen PORT_PLACEHOLDER default_server;
    server_name _;
    root /app/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;

    # Logging
    access_log /dev/stdout;
    error_log /dev/stderr;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Replace PORT placeholder with actual PORT value
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/sites-available/default

# Link to sites-enabled if needed
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Create minimal nginx.conf if it doesn't exist or is problematic
cat > /etc/nginx/nginx.conf <<'NGINX_CONF'
user www-data;
worker_processes auto;
pid /tmp/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    access_log /dev/stdout;
    error_log /dev/stderr;

    sendfile on;
    tcp_nopush on;
    keepalive_timeout 65;
    client_max_body_size 100M;

    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    include /etc/nginx/sites-enabled/*;
}
NGINX_CONF

# Test nginx configuration
echo "Testing Nginx configuration..."
nginx -t

# Start nginx in foreground
echo "Starting Nginx on port $PORT..."
exec nginx -g 'daemon off;'