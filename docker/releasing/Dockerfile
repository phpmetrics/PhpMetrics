FROM composer/composer:2-bin AS composer

# please do not use alpine here: We need a debian based image for .deb distribution
FROM php:8.1-cli-bullseye

# Installing ruby, docker, semver and required dependencies
RUN apt update \
    && apt install -y \
      ruby ruby-dev make build-essential git gnupg2 debhelper \
      apt-transport-https ca-certificates curl gnupg2 software-properties-common \
    && gem install semver \
    && echo "phar.readonly=0" > /usr/local/etc/php/conf.d/phar.ini \
    && curl -fsSL https://download.docker.com/linux/debian/gpg | apt-key add - \
    && add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable" \
    && apt update \
    && apt install -y docker-ce

RUN git config --global --add safe.directory /app

# Installing composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"
COPY --from=composer /composer /usr/bin/composer
