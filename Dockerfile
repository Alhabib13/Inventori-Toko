FROM php:8.5-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    git \
    curl \
    unzip \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

EXPOSE 9000

CMD ["php-fpm"]
