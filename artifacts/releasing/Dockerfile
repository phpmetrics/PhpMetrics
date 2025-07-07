FROM composer/composer:2-bin AS composer

# please do not use alpine here: We need a debian based image for .deb distribution
FROM php:8.4-cli-bullseye

# Installing ruby, semver and required dependencies
RUN apt update \
    && apt install -y ruby ruby-dev make build-essential git gnupg2 debhelper \
    && gem install semver \
    && echo "phar.readonly=0" > /usr/local/etc/php/conf.d/phar.ini

RUN git config --global --add safe.directory /app

# Installing musl-wrapper
RUN apt install -y musl-dev musl-tools \
    && ln -s /usr/bin/musl-gcc /usr/local/bin/musl-gcc \
    && ln -s /usr/bin/musl-g++ /usr/local/bin/musl-g++

# Installing composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"
COPY --from=composer /composer /usr/bin/composer
