FROM composer:latest as composer

RUN mkdir /ppm && cd /ppm && composer require php-pm/httpkernel-adapter:dev-master

FROM php:7.4-cli

RUN apt-get update && apt-get install -y libpq-dev libicu-dev \
    && docker-php-ext-install intl pdo_pgsql pcntl opcache \
    && pecl install xdebug && pecl install apcu \
    && docker-php-ext-enable xdebug apcu


COPY php.ini.dev /usr/local/etc/php/conf.d/99-overrides.ini

EXPOSE 80

COPY --from=composer /ppm /ppm

WORKDIR /application

ENTRYPOINT ["/ppm/vendor/bin/ppm", "start", "--host=0.0.0.0", "--port=80", "--ansi", "--debug=1", "--static-directory=public/"]
