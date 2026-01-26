FROM php:8.1-fpm

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    ca-certificates \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    pkg-config \
    zip \
    unzip \
    git \
    build-essential \
    autoconf \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql mbstring xml zip opcache \
  && apt-get purge -y --auto-remove build-essential autoconf pkg-config \
  && rm -rf /var/lib/apt/lists/*

# Ambil composer dari image resmi composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files dulu agar cache layer lebih efisien
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-ansi

# Copy sisa source
COPY . .

CMD ["php-fpm"]