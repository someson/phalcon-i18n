FROM php:8.0-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    zip \
    libssl-dev \
    git \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

ENV APCU_VERSION=5.1.21
RUN set -xe \
    && pecl install apcu-${APCU_VERSION} xdebug \
    && docker-php-ext-enable xdebug apcu \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-install -j$(nproc) \
        zip \
        calendar \
        intl \
        pdo_mysql \
    && pecl clear-cache

ENV PHALCON_VERSION=5.0.0beta3 \
    PSR_VERSION=1.1.0
RUN set -xe \
    && curl -LO https://github.com/jbboehr/php-psr/archive/refs/tags/v${PSR_VERSION}.tar.gz \
    && tar xzf ${PWD}/v${PSR_VERSION}.tar.gz \
    && curl -LO https://github.com/phalcon/cphalcon/archive/v${PHALCON_VERSION}.tar.gz \
    && tar xzf ${PWD}/v${PHALCON_VERSION}.tar.gz \
    && docker-php-ext-install -j $(getconf _NPROCESSORS_ONLN) \
        ${PWD}/php-psr-${PSR_VERSION} \
        ${PWD}/cphalcon-${PHALCON_VERSION}/build/phalcon \
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
