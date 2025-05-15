FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    icu-dev

RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

RUN composer run-script post-install-cmd

RUN mkdir -p var/cache var/log && \
    chown -R www-data:www-data var

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
