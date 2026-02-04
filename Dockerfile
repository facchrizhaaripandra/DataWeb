FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    tesseract-ocr \
    nginx \
    libpq-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Configure PHP-FPM to listen on TCP
RUN sed -i 's/listen = \/run\/php\/php8.3-fpm.sock/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf || \
    echo "listen = 127.0.0.1:9000" >> /usr/local/etc/php-fpm.d/www.conf

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-scripts --ignore-platform-reqs

# Copy application code
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize

# Install Node dependencies and build assets
RUN npm ci && npm run build

# Set permissions
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Make start script executable
RUN chmod +x start.sh

# Expose port (Railway will use $PORT)
EXPOSE 8080

# Start command
CMD ["./start.sh"]