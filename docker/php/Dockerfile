# docker/php/Dockerfile
FROM php:8.4-fpm-alpine

ARG INSTALL_XDEBUG=false

RUN apk add --no-cache \
        bash \
        git \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        imagemagick \
        imagemagick-dev \
        shadow \
        $PHPIZE_DEPS

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
        apcu \
        bcmath \
        gd \
        imagick \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        redis \
        zip

RUN if [ "$INSTALL_XDEBUG" = "true" ]; then install-php-extensions xdebug-^3.4; fi

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json symfony.lock composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --no-scripts

COPY docker/php/conf.d/dev.ini /usr/local/etc/php/conf.d/zz-dev.ini

COPY . ./

RUN mkdir -p var/cache var/log \
        && chown -R www-data:www-data var public \
        && if [ -d vendor ]; then chown -R www-data:www-data vendor; fi
