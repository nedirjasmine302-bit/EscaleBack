FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    $PHPIZE_DEPS \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libssl-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_mysql mysqli intl zip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
