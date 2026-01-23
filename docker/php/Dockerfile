FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    zip \
    unzip \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client \
    && docker-php-ext-install pdo pdo_mysql mysqli intl zip opcache

WORKDIR /var/www/html
