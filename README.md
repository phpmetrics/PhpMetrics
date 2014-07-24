# PhpMetrics

Gives metrics about PHP project and classes.

[![License](https://poser.pugx.org/halleck45/php-metrics/license.png)](https://packagist.org/packages/halleck45/php-metrics)
[![Build Status](https://secure.travis-ci.org/Halleck45/PhpMetrics.png)](http://travis-ci.org/Halleck45/PhpMetrics)  [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Halleck45/PhpMetrics/badges/quality-score.png?s=b825f35680c0a469333da2c963226828fed135ba)](https://scrutinizer-ci.com/g/Halleck45/PhpMetrics/)
[![Latest Stable Version](https://poser.pugx.org/halleck45/php-metrics/v/stable.png)](https://packagist.org/packages/halleck45/php-metrics)
[![Dependency Status](https://www.versioneye.com/user/projects/534fe1f9fe0d0774a8000815/badge.png)](https://www.versioneye.com/user/projects/534fe1f9fe0d0774a8000815)

+ [Installation](#installation)
+ [Usage](#usage)
+ [Conditions of failure](#conditions-of-failure)
+ [IDE integration](#ide-integration)
+ [Jenkins and PIC integration](#jenkins-and-pic-integration)





# Installation

With Composer:

    php composer.phar global require 'halleck45/phpmetrics=@dev'

You can also download [PhpMetrics as phar archive](https://github.com/Halleck45/PhpMetrics/raw/master/build/metrics.phar).

# Usage

The command command `vendor/bin/phpmetrics <folder or filename>` will output:

![Standard report](http://halleck45.github.io/PhpMetrics/images/report-standard.png)

If you want to get the summary HTML report (with charts):

    php ./bin/metrics.php --report-html=/path/of/your/choice.html <folder or filename>

No panic : you can read the [How to read the HTML report page](http://halleck45.github.io/PhpMetrics/documentation/how-to-read-report.html)

## Conditions of failure

Customizing the conditions of failure is very easy with the`--failure-condition` option. For example:

    --failure-condition="average.maintenabilityIndex < 100 or sum.loc > 10000"

With this example, PhpMetrics script returns 1 if the average of Maintenability index is lower than 100
or if the total number of lines of code is greater than 10000.

You can also work with package:

    --failure-condition="My/Package1/XXXX.average.bugs > 0.35"

Remember that in PhpMetrics packages are file oriented (and not object oriented).

Conditions are evaluated with the [Hoa Ruler](https://github.com/hoaproject/Ruler) component. Available operators are
`and`, `or`, `xor`, `not`, `=` (`is` as an alias), `!=`, `>`, `>=`, `<`, `<=`, `in` and `sum`

List of availables metrics is documented [here](http://halleck45.github.io/PhpMetrics/documentation/index.html).


## IDE integration

+ [PhpMetrics plugin for PhpStom](http://plugins.jetbrains.com/plugin/7500)

## Jenkins and IC integration

You'll find a complete tutorial in the [documentation](http://halleck45.github.io/PhpMetrics/documentation/jenkins.html)

You can easily export results to XML with the `--report-xml` option:

    php ./bin/metrics.php --report-xml=/path/of/your/choice.xml <folder or filename>

You can also export results as violations (MessDetector report), in XML format with the `--violations-xml` option:

    php ./bin/metrics.php --violations-xml=/path/of/your/choice.xml <folder or filename>

# Contribute

In order to run unit tests, please install the dev dependencies:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --dev

Then, to run the test suite:

    ./vendor/bin/phpunit

# Author

+ Jean-François Lépine <[www.lepine.pro](http://www.lepine.pro)>

# Licence

See the LICENCE file.
