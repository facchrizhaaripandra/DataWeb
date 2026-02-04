#!/bin/bash

# Start PHP-FPM in background
php-fpm -D

# Wait for PHP-FPM to be ready
sleep 2

# Create nginx config with correct PORT
cat > /etc/nginx/sites-available/default <<EOF
server {
    listen $PORT default_server;
    root /app/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF

# Start nginx in foreground
nginx -g 'daemon off;'