#!/bin/bash
set -e

echo "Starting PHP-FPM..."

# Configure PHP-FPM to listen on TCP
if [ -f /usr/local/etc/php-fpm.d/www.conf ]; then
    sed -i 's/listen = .*/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf
fi

# Start PHP-FPM in background
php-fpm -D

# Wait for PHP-FPM to be ready
sleep 3

echo "PHP-FPM started successfully on 127.0.0.1:9000"

# Create nginx directories
mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled /var/log/nginx

# Create nginx configuration
echo "Creating Nginx configuration for port $PORT..."
cat > /etc/nginx/nginx.conf <<'NGINX'
worker_processes auto;
pid /tmp/nginx.pid;
error_log /dev/stderr;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    access_log /dev/stdout;
    error_log /dev/stderr;
    
    sendfile on;
    keepalive_timeout 65;
    client_max_body_size 100M;
    
    server {
        listen PORT_PLACEHOLDER default_server;
        server_name _;
        root /app/public;
        index index.php index.html;
        
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
        
        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
        
        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
}
NGINX

# Replace PORT placeholder
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/nginx.conf

# Test nginx configuration
echo "Testing Nginx configuration..."
nginx -t

# Start nginx in foreground
echo "Starting Nginx on port $PORT..."
exec nginx -g 'daemon off;'