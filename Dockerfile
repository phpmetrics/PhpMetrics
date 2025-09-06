FROM php:8.4

LABEL org.opencontainers.image.authors="niconoe- <nicolas.giraud.dev@gmail.com>"

COPY releases/phpmetrics.phar /usr/local/bin/phpmetrics

RUN chmod +x /usr/local/bin/phpmetrics \
    # Install git to be able to use option "--git".
    && apt-get update && apt-get install -y git \
    && rm -rf /var/lib/apt/lists/*

VOLUME ["/app"]
WORKDIR /app

ENTRYPOINT ["phpmetrics"]
CMD ["--version"]
