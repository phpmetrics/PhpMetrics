FROM php:8.1-alpine

ENV COMPOSER_VENDOR_DIR=vendor COMPOSER_CACHE_DIR=/tmp/.composer COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apk update && \
    # Download depends:
    # - zlib-dev, libzip-dev (used for zip PHP extension required for XDebug).
    # - icu (used for intl, required for PHP Infection).
    # - yaml-dev (to install ext-yaml into PHP).
    apk add --quiet --no-cache zlib-dev libzip-dev icu yaml-dev && \
    # Download dev-tools
    # - g++, make (used to rebuild PHP with extensions)
    # - autoconf (used to auto-configure the g++ builder and the configuration of pcntl)
    # - curl (to fetch the PHP Infection release)
    # - jq (to read results from GitHub API and select the good release to fetch)
    # - icu-dev (to install intl for PHP Infection)
    # - packages from $PHPIZE_DEPS (for xdebug installation)
    apk add --quiet --no-cache --update --virtual .dev-tools icu-dev g++ make autoconf curl jq $PHPIZE_DEPS && \
    pecl -q install xdebug yaml && \
    # Add pcntl to allow multiple processes to be executed at the same time.
    docker-php-ext-configure pcntl --enable-pcntl > /dev/null && \
    docker-php-ext-configure intl > /dev/null && \
    docker-php-ext-install -j$(nproc) pcntl zip intl > /dev/null && \
    docker-php-ext-enable xdebug intl yaml > /dev/null && \
    docker-php-source delete > /dev/null && \
    # Configure XDebug for PHPUnit and Infection.
    echo "xdebug.client_port=9003" >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.discover_client_host=0" >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.mode=coverage" >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini && \
    # Remove memory limit.
    echo "memory_limit=-1" >> ${PHP_INI_DIR}/conf.d/99_memory-limit.ini && \
    # Allow phar writing.
    echo 'phar.readonly=0' >> ${PHP_INI_DIR}/conf.d/docker-php-phar-readonly.ini && \
    # Fetching the PHP Infection from release to fetch.
    echo "Looking for the latest PHP Infection 0.26.x version…" && \
    RELEASES=$(curl -sS https://api.github.com/repos/infection/infection/releases | jq --compact-output --raw-output 'map(select( .tag_name | test("^0.26.[0-9]+$") ))') && \
    NB_RELEASES=$(echo "${RELEASES}" | jq --compact-output --raw-output 'length') && \
    if [ "${NB_RELEASES}" == "0" ]; then echo "ERROR: Not a single patch version found for minor version 0.26 of PHP Infection."; exit 1; fi && \
    LATEST_FOR_MINOR=$(echo "${RELEASES}" | jq --compact-output --raw-output '. | sort_by( .tag_name ) | .[-1] | {tag: .tag_name, src: .assets[]|select(.name == "infection.phar").browser_download_url}') && \
    INFECTION_TAG=$(echo "${LATEST_FOR_MINOR}" | jq --compact-output --raw-output '.tag') && \
    URL_SRC=$(echo "${LATEST_FOR_MINOR}" | jq --compact-output --raw-output '.src') && \
    echo "Downloading version ${INFECTION_TAG}…" && \
    # Installing the PHP Infection fetched.
    curl -LSso /usr/local/bin/infection ${URL_SRC} && \
    chmod +x /usr/local/bin/infection && \
    apk del --quiet --purge .dev-tools && \
    rm -rf /var/lib/apt/lists/* /var/cache/apk/* /var/tmp/* /tmp/* /usr/local/bin/infection.asc

ENV PATH=$PATH:/opt/project/vendor/bin

VOLUME ["/opt/project"]
WORKDIR /opt/project

# Commands to use are:
#- composer (for Composer)
#- infection (for PHP Infection)
#- phpstan (for PHPStan)
#- phpunit (for PHPUnit)
#- psalm (for Psalm)
#- phpcs (for PHP Code Sniffer - analysis)
#- phpcbf (for PHP Code Sniffer - fix)
