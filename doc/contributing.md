# Contribute

In order to run unit tests, please install the dev dependencies:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

Then, in order to run the test suite:

    ./vendor/bin/phpunit

Thanks for your help.

## Releasing

Please NEVER tag manually.

### Requirements

+ You will need to install `semver`

    gem install semver
    
+ Please disable `phar.readonly` in your `php.ini` file
  
### Usage

These commands will create phar, debian and binary release, 
then run all tests and push new release to Github :

    make tag version=<VERSION>
    # <VERSION> can be `major`, `minor` or `patch`
    make release
