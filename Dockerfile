FROM composer:latest as composer

RUN mkdir /ppm && cd /ppm && composer require php-pm/httpkernel-adapter:2.2.1

RUN mkdir /application

COPY src /application/src

COPY composer.json /application/composer.json
COPY composer.lock /application/composer.lock
COPY symfony.lock /application/symfony.lock

RUN cd /application && composer install -o --no-dev --no-scripts

FROM php:8.0-cli

RUN apt-get update && apt-get install -y libpq-dev libicu-dev \
    && docker-php-ext-install intl pdo_pgsql pcntl opcache \
    && pecl install apcu \
    && docker-php-ext-enable apcu

COPY docker/php-pm/php.ini /usr/local/etc/php/conf.d/99-overrides.ini

EXPOSE 8080

WORKDIR /application

COPY . /application

# Can't ignore because we want it from the other layer :\
RUN rm -rf vendor/

COPY --from=composer /ppm /ppm
COPY --from=composer /application/vendor /application/vendor/

ENV APP_ENV=prod

RUN ./bin/console cache:warmup

#TODO: Set port and workers from ENV vars
ENTRYPOINT ["/ppm/vendor/bin/ppm", "start", "--host=0.0.0.0", "--port=8080", "--workers=2", "--app-env=prod", "--static-directory=public/"]
