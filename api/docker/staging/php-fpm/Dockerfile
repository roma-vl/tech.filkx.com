FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    zlib1g-dev \
    zip \
    libzip-dev \
    libicu-dev \
    vim \
    git \
    curl \
    unzip \
    ffmpeg \
    libjpeg-dev \
    libpng-dev \
    libwebp-dev \
    libfreetype6-dev \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install zip pdo pdo_pgsql intl exif gd

# Нормальний php.ini
RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

# Конфіги
COPY ./common/php/conf.d /usr/local/etc/php

WORKDIR /app
COPY ./ ./
