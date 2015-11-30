FROM php:7-cli

RUN apt-get update && apt-get install -y zlib1g-dev libicu-dev g++
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN apt-get install -y graphviz
RUN docker-php-ext-configure mbstring
RUN docker-php-ext-install mbstring

VOLUME ./:/var/www
WORKDIR /var/www
