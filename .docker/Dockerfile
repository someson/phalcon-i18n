FROM php:8.1-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    libzip-dev \
    libyaml-dev \
    libssl-dev \
    libicu-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && install-php-extensions yaml

ENV APCU_VERSION=5.1.22
RUN set -xe \
    && pecl install xdebug apcu-${APCU_VERSION} \
    && docker-php-ext-enable xdebug apcu \
    && docker-php-ext-install -j$(nproc) zip calendar intl pdo_mysql \
    && pecl clear-cache

ENV PHALCON_VERSION=5.1.3 \
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

WORKDIR /var/www
