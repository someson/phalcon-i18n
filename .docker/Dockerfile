FROM php:7.4-fpm-alpine3.13

ENV PHALCON_VERSION=4.1.3 \
    PHALCON_EXT_PATH=php7/64bits \
    PSR_VERSION=1.2.0

RUN apk update && rm -rf /var/cache/apk/*
RUN apk add git \
    && apk add --no-cache --virtual .persistent-deps libxml2-dev zip libzip-dev icu-libs

RUN set -xe \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev icu-libs \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-install -j$(nproc) calendar intl zip \
    && pecl clear-cache \
    && apk del .build-deps \
    && docker-php-source delete \
    && rm -rf /tmp/* /var/cache/apk/*

RUN set -xe \
    && curl -LO https://github.com/jbboehr/php-psr/archive/v${PSR_VERSION}.tar.gz \
    && tar xzf ${PWD}/v${PSR_VERSION}.tar.gz \
    && curl -LO https://github.com/phalcon/cphalcon/archive/v${PHALCON_VERSION}.tar.gz \
    && tar xzf ${PWD}/v${PHALCON_VERSION}.tar.gz \
    && docker-php-ext-install -j $(getconf _NPROCESSORS_ONLN) \
        ${PWD}/php-psr-${PSR_VERSION} \
        ${PWD}/cphalcon-${PHALCON_VERSION}/build/${PHALCON_EXT_PATH} \
    && rm -r \
        ${PWD}/v${PSR_VERSION}.tar.gz \
        ${PWD}/php-psr-${PSR_VERSION} \
        ${PWD}/v${PHALCON_VERSION}.tar.gz \
        ${PWD}/cphalcon-${PHALCON_VERSION} \
    && php -m

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/php-override.ini
RUN touch /var/log/remote.log && chmod 666 /var/log/remote.log

WORKDIR /var/www/html
