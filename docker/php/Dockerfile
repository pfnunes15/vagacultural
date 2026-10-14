# syntax=docker/dockerfile:1
FROM php:8.4-fpm-alpine

# System deps + PHP extensions required by Laravel + MySQL
# GD (with freetype/jpeg) powers image handling and the test image factory.
RUN apk add --no-cache \
        bash git icu-dev libzip-dev zip unzip oniguruma-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath intl zip opcache gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Composer (from official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Sensible PHP + opcache defaults for dev
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-vaga.ini

WORKDIR /var/www/html

# Match host uid to avoid permission issues on mounted volumes (override at build)
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} app 2>/dev/null || true \
    && adduser -D -u ${UID} -G www-data app 2>/dev/null || true

EXPOSE 9000
CMD ["php-fpm"]
