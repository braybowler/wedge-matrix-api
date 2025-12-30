FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git \
    unzip \
    libpng-dev \
    oniguruma-dev \
    libzip-dev \
    mysql-client \
 && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    bcmath \
    gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Install deps first (layer caching)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Copy app
COPY . .

# Optimize Laravel
RUN php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear

RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
