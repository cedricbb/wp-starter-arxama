FROM php:8.3-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    bash \
    git \
    zip \
    unzip \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client \
    curl

# PHP extensions required by WordPress / Bedrock
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    intl \
    zip \
    opcache

# Composer (official binary)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
