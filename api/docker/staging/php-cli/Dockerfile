FROM php:8.4-cli

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

## Install Xdebug only for development
#RUN pecl install xdebug \
#    && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Нормальний php.ini
RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY ./common/php/conf.d/ "$PHP_INI_DIR/"

# Security & permissions
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app
USER nobody
