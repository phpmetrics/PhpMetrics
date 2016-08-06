# PhpMetrics

Gives metrics about PHP project and classes.

[![License](https://poser.pugx.org/phpmetrics/phpmetrics/license.svg)](https://packagist.org/packages/phpmetrics/phpmetrics)
[![Build Status](https://secure.travis-ci.org/phpmetrics/PhpMetrics.svg)](http://travis-ci.org/phpmetrics/PhpMetrics)
[![Latest Stable Version](https://poser.pugx.org/phpmetrics/phpmetrics/v/stable.svg)](https://packagist.org/packages/phpmetrics/phpmetrics)
[![Dependency Status](https://www.versioneye.com/user/projects/534fe1f9fe0d0774a8000815/badge.svg)](https://www.versioneye.com/user/projects/534fe1f9fe0d0774a8000815)


# Installation

#### As a phar archive:

You can install the [.phar](https://github.com/Halleck45/PhpMetrics/raw/master/build/phpmetrics.phar) package by command line running the following commands:

```bash
wget https://github.com/phpmetrics/PhpMetrics/raw/master/build/phpmetrics.phar
chmod +x phpmetrics.phar
mv phpmetrics.phar /usr/local/bin/phpmetrics
```

#### As a composer dependency:

    composer global require 'phpmetrics/phpmetrics'

# Usage

> Do not hesitate to visit the [official documentation](http://www.phpmetrics.org).

The command command `phpmetrics --report-html=./log <folder or filename> ` will generate HTML report in the `./log`directory.

![Standard report](http://phpmetrics.github.io/PhpMetrics/images/report-standard.png)

## Compatibility

PhpMetrics can parse PHP code from **PHP 5.3 to PHP 7.x**.

## IDE integration

+ [PhpMetrics plugin for PhpStorm](http://plugins.jetbrains.com/plugin/7500)

# Contribute

In order to run unit tests, please install the dev dependencies:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

Then, in order to run the test suite:

    ./vendor/bin/phpunit

Finally, build the phar:

    make build

# Author

+ Jean-François Lépine <[www.lepine.pro](http://www.lepine.pro)>

# License

See the LICENSE file.
