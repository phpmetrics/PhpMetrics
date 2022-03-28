FROM php:8.1-alpine

MAINTAINER "niconoe-" <nicolas.giraud.dev@gmail.com>

COPY releases/phpmetrics.phar /usr/local/bin/phpmetrics

RUN set -eux \
    && chmod +x /usr/local/bin/phpmetrics \
    # Install git to be able to use option "--git".
    && apk update && apk add git \
    && rm -rf /var/cache/apk/* /var/tmp/* /tmp/*

VOLUME ["/app"]
WORKDIR /app

ENTRYPOINT ["phpmetrics"]
CMD ["--version"]
