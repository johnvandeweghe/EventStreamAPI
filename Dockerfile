FROM composer:latest as composer

RUN mkdir /ppm && cd /ppm && composer require php-pm/httpkernel-adapter:2.0.6

RUN mkdir /application

COPY src /application/src

COPY composer.json /application/composer.json
COPY composer.lock /application/composer.lock
COPY symfony.lock /application/symfony.lock

RUN cd /application && composer install -o --no-dev --no-scripts

FROM php:7.4-cli

RUN apt-get update && apt-get install -y libpq-dev libicu-dev \
    && docker-php-ext-install intl pdo_pgsql pcntl opcache \
    && pecl install apcu \
    && docker-php-ext-enable apcu

COPY docker/php-pm/php.ini /usr/local/etc/php/conf.d/99-overrides.ini

EXPOSE 8080

COPY --from=composer /ppm /ppm
COPY --from=composer /application/vendor /application/vendor/

WORKDIR /application

COPY . /application

ENV APP_ENV=prod

RUN ./bin/console cache:warmup

ENTRYPOINT ["/ppm/vendor/bin/ppm", "start", "--host=0.0.0.0", "--port=8080", "--workers=2", "--app-env=prod", "--static-directory=public/"]
