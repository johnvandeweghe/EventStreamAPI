FROM composer:latest as composer

RUN mkdir /ppm && cd /ppm && composer require php-pm/httpkernel-adapter:2.0.6

FROM php:7.4-cli

RUN apt-get update && apt-get install -y libpq-dev libicu-dev \
    && docker-php-ext-install intl pdo_pgsql pcntl

COPY docker/php-pm/php.ini /usr/local/etc/php/conf.d/99-overrides.ini

EXPOSE 8080

COPY --from=composer /ppm /ppm

WORKDIR /application

ENTRYPOINT ["/ppm/vendor/bin/ppm", "start", "--host=0.0.0.0", "--port=8080", "--ansi", "--static-directory=public/"]
