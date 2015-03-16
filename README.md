# PhpMetrics

Gives metrics about PHP project and classes.

[![License](https://poser.pugx.org/halleck45/php-metrics/license.svg)](https://packagist.org/packages/halleck45/php-metrics)
[![Build Status](https://secure.travis-ci.org/Halleck45/PhpMetrics.svg)](http://travis-ci.org/Halleck45/PhpMetrics)  [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Halleck45/PhpMetrics/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Halleck45/PhpMetrics/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/halleck45/php-metrics/v/stable.svg)](https://packagist.org/packages/halleck45/php-metrics)
[![Dependency Status](https://www.versioneye.com/user/projects/534fe1f9fe0d0774a8000815/badge.svg)](https://www.versioneye.com/user/projects/534fe1f9fe0d0774a8000815)

+ [Installation](#installation)
+ [Usage](#usage)
+ [Conditions of failure](#conditions-of-failure)
+ [IDE integration](#ide-integration)
+ [Jenkins and CI](#jenkins-and-ci)
+ [Configuration file](#configuration-file)





# Installation

As phar archive:

```bash
wget https://github.com/Halleck45/PhpMetrics/raw/master/build/phpmetrics.phar
chmod +x phpmetrics.phar
mv phpmetrics.phar /usr/local/bin/phpmetrics
```

With Composer (Make sure you have `~/.composer/vendor/bin/` in your path):

    php composer.phar global require 'halleck45/phpmetrics'

# Usage

> Do not hesitate to visit the [official documentation](http://www.phpmetrics.org/documentation/index.html).

The command command `phpmetrics <folder or filename>` will output:

![Standard report](http://halleck45.github.io/PhpMetrics/images/report-standard.png)

If you want to get the summary HTML report (with charts):

    phpmetrics --report-html=/path/of/your/choice.html <folder or filename>

No panic : you can read the [How to read the HTML report page](http://halleck45.github.io/PhpMetrics/documentation/how-to-read-report.html)

> If you need a pure string representation of the reports in StdOut, just use `phpmetrics -q --report-xml=php://stdout <folder or filename>`

## Conditions of failure

Customizing the conditions of failure is very easy with the`--failure-condition` option. For example:

    --failure-condition="average.maintainabilityIndex < 100 or sum.loc > 10000"

With this example, PhpMetrics script returns 1 if the average of Maintainability index is lower than 100
or if the total number of lines of code is greater than 10000.

You can also work with package:

    --failure-condition="My/Package1/XXXX.average.bugs > 0.35"

Remember that in PhpMetrics packages are file oriented (and not object oriented).

Conditions are evaluated with the [Hoa Ruler](https://github.com/hoaproject/Ruler) component. Available operators are
`and`, `or`, `xor`, `not`, `=` (`is` as an alias), `!=`, `>`, `>=`, `<`, `<=`, `in` and `sum`

List of availables metrics is documented [here](http://halleck45.github.io/PhpMetrics/documentation/index.html).


## IDE integration

+ [PhpMetrics plugin for PhpStorm](http://plugins.jetbrains.com/plugin/7500)

## Jenkins and CI

You'll find a complete tutorial in the [documentation](http://halleck45.github.io/PhpMetrics/documentation/jenkins.html)

You can easily export results to XML with the `--report-xml` option:

    phpmetrics --report-xml=/path/of/your/choice.xml <folder or filename>

You can also export results as violations (MessDetector report), in XML format with the `--violations-xml` option:

    phpmetrics --violations-xml=/path/of/your/choice.xml <folder or filename>

## Configuration file

You can customize configuration with the `--config=<file>` option.

The file should be a valid yaml file. For example:

    # file <my-config.yml>
    myconfig:
        # paths to explore
        path:
            extensions: php|inc
            exclude: Features|Tests|tests

        # report and violations files
        logging:
            report:
                xml:    ./log/phpmetrics.xml
                html:   ./log/phpmetrics.html
                csv:    ./log/phpmetrics.csv
            violations:
                xml:    ./log/violations.xml
            chart:
                bubbles: ./log/bubbles.svg

        # condition of failure
        failure: average.maintainabilityIndex < 50 or sum.loc > 10000

        # rules used for color
        rules:
          cyclomaticComplexity: [ 10, 6, 2 ]
          maintainabilityIndex: [ 0, 69, 85 ]
          [...]

Each rule is composed from three values.

+ If `A < B < C` : `A`: min, `B`: yellow limit, `C`: max
+ If `A > B > C` : `A`: max, `B`: yellow limit, `C`: min

# Contribute

In order to run unit tests, please install the dev dependencies:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
    gem install semver

Then, in order to run the test suite:

    ./vendor/bin/phpunit

Finally, build the phar:

    make

# Author

+ Jean-François Lépine <[www.lepine.pro](http://www.lepine.pro)>

# License

See the LICENSE file.
