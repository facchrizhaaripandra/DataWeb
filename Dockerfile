FROM php:8.1-fpm

# Install system deps dan library untuk GD + beberapa ekstensi umum
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    zip \
    unzip \
    git \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql mbstring xml zip opcache \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Ambil composer dari image resmi composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files dulu agar cache layer lebih efisien
COPY composer.json composer.lock ./
# Jalankan composer install — ext-gd sudah tersedia di layer ini
RUN composer install --no-dev --prefer-dist --no-interaction --no-ansi

# Copy sisa source
COPY . .

CMD ["php-fpm"]