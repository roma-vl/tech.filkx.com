FROM php:8.5-cli

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
    && docker-php-ext-configure zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install zip pdo pdo_pgsql intl exif gd \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# PHP config
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY ./common/php/conf.d/ "$PHP_INI_DIR/conf.d/"

# Security & permissions
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app
USER nobody
