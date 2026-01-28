FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git \
    unzip \
    libpng-dev \
    oniguruma-dev \
    libzip-dev \
    icu-dev \
    mysql-client \
 && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    bcmath \
    gd \
    intl


COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENV COMPOSER_MEMORY_LIMIT=-1

COPY composer.json composer.lock ./
RUN composer install -vvv \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts


COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
