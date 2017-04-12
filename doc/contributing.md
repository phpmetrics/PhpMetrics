# Contribute

In order to run unit tests, please install the dev dependencies:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

Then, in order to run the test suite:

    ./vendor/bin/phpunit

Thanks for your help !

## Releasing

Please NEVER tag manually.

First, changes sources according new tag:

    make tag <VERSION>
    
version can be `major`, `minor` or `patch`

Then create release and Git tag with

    make release
