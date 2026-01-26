FROM php:8.3-fpm

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
  && apt-get install -y --no-install-recommends --fix-missing \
    ca-certificates \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    libpq-dev \
    pkg-config \
    zip \
    unzip \
    git \
    build-essential \
    autoconf \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" gd pdo pdo_pgsql pdo_mysql mbstring xml zip opcache \
  && apt-get purge -y --auto-remove build-essential autoconf pkg-config \
  && rm -rf /var/lib/apt/lists/*

# Ambil composer dari image resmi composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files dulu agar cache layer lebih efisien
COPY composer.json composer.lock artisan ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-ansi --no-scripts

# Copy sisa source
COPY . .

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=${PORT:-8000}"]
