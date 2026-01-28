FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx \
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

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
