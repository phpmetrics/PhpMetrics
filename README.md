# PhpMetrics

Gives metrics about PHP project and classes.

[![License](https://poser.pugx.org/phpmetrics/phpmetrics/license.svg)](https://packagist.org/packages/phpmetrics/phpmetrics)
[![Build Status](https://secure.travis-ci.org/phpmetrics/PhpMetrics.svg)](http://travis-ci.org/phpmetrics/PhpMetrics)  [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phpmetrics/PhpMetrics/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpmetrics/PhpMetrics/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/phpmetrics/phpmetrics/v/stable.svg)](https://packagist.org/packages/phpmetrics/phpmetrics)
[![Dependency Status](https://www.versioneye.com/user/projects/534fe1f9fe0d0774a8000815/badge.svg)](https://www.versioneye.com/user/projects/534fe1f9fe0d0774a8000815)

+ [Installation](#installation)
+ [Usage](#usage)
+ [Conditions of failure](#conditions-of-failure)
+ [IDE integration](#ide-integration)
+ [Jenkins and CI](#jenkins-and-ci)
+ [Configuration](#configuration)





# Installation

#### As a phar archive:

You can install the [.phar](https://github.com/Halleck45/PhpMetrics/raw/master/build/phpmetrics.phar) package by command line running the following commands:

```bash
wget https://github.com/phpmetrics/PhpMetrics/raw/master/build/phpmetrics.phar
chmod +x phpmetrics.phar
mv phpmetrics.phar /usr/local/bin/phpmetrics
```

Then, if you need to update:

    phpmetrics self-update


#### As a composer dependency:

    composer global require 'phpmetrics/phpmetrics'

# Usage

> Do not hesitate to visit the [official documentation](http://www.phpmetrics.org).

The command command `phpmetrics --report-cli <folder or filename> ` will output:

![Standard report](http://phpmetrics.github.io/PhpMetrics/images/report-standard.png)

If you want to get the summary HTML report (with charts):

    phpmetrics --report-html=/path/of/your/choice.html <folder or filename>

No panic : you can read the [How to read the HTML report page](http://www.phpmetrics.org/documentation/how-to-read-report.html)

> If you need a pure string representation of the reports in StdOut, just use `phpmetrics -q --report-xml=php://stdout <folder or filename>`

## Compatibility

PhpMetrics can parse PHP code from **PHP 5.3 to PHP 7.x**.

> I want to parse PHP7 code but I use PHP 5.x

You can parse PHP7 code, but your interpreter will detect syntax errors in your code. To prevent this, please 
use the `--ignore-errors` option.

## Extensions / Plugins

PhpMetrics supports plugins. Use the `--plugins=<path-of-plugin1>` option, or update your config file as following:

```
  plugins:
    - <path-of-plugin1.php`

```

Official plugins:

+ [Symfony2](https://github.com/phpmetrics/SymfonyExtension)
+ [Composer](https://github.com/phpmetrics/ComposerExtension)

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

List of availables metrics is documented [here](http://www.phpmetrics.org/documentation/index.html).


## IDE integration

+ [PhpMetrics plugin for PhpStorm](http://plugins.jetbrains.com/plugin/7500)

## Jenkins and CI

You'll find a complete tutorial in the [documentation](http://www.phpmetrics.org/documentation/jenkins.html)

You can easily export results to XML with the `--report-xml` option:

    phpmetrics --report-xml=/path/of/your/choice.xml <folder or filename>

You can also export results as violations (MessDetector report), in XML format with the `--violations-xml` option:

    phpmetrics --violations-xml=/path/of/your/choice.xml <folder or filename>

## Configuration

### Configuration options

* `--report-html` - Path to save report in HTML format. Example: --report-html=/tmp/report.html
* `--report-xml` - Path to save summary report in XML format. Example: --report-xml=/tmp/report.xml
* `--report-cli` - Enable report in terminal.
* `--violations-xml` - Path to save violations in XML format. Example: --violations-xml=/tmp/report.xml
* `--report-csv` - Path to save summary report in CSV format. Example: --report-csv=/tmp/report.csv
* `--report-json` - Path to save detailed report in JSON format. Example: --report-json=/tmp/report.json
* `--chart-bubbles` - Path to save Bubbles chart, in SVG format. Example: --chart-bubbles=/tmp/chart.svg. Graphviz **IS** required
* `--level` - Depth of summary report.
* `--extensions` - Regex of extensions to include.
* `--excluded-dirs` - Regex of subdirectories to exclude.
* `--symlinks` - Enable following symlinks.
* `--without-oop` - If provided, tool will not extract any information about OOP model (faster).
* `--ignore-errors` - If provided, files will be analyzed even with syntax errors
* `--failure-condition` - Optional failure condition, in english. Example: --failure-condition="average.maintainabilityIndex < 50 or sum.loc > 10000"
* `--config` - Config file (YAML). Example: --config=myconfig.yml
* `--template-title` - Title for the HTML summary report.

A complete example command line:

`phpmetrics --report-html=report.html --report-xml=report.xml --report-cli=true --violations-xml=violations.xml 
--report-csv=report.csv --report-json=report.json --chart-bubbles=chart.svg --level=3 --extensions=php|inc --excluded-dirs="cache|logs"
--symlinks=true --without-oop=true --failure-condition="average.maintainabilityIndex < 50 or sum.loc > 10000" --template-title="My Report" /path/to/source`

### Configuration file

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

        # rules used for color ([ critical, warning, good ])
        rules:
          cyclomaticComplexity: [ 10, 6, 2 ]
          maintainabilityIndex: [ 0, 69, 85 ]
          [...]

Each rule is composed from three values.

+ If `A < B < C` : `A`: min, `B`: yellow limit, `C`: max
+ If `A > B > C` : `A`: max, `B`: yellow limit, `C`: min

You can save the configuration in a `.phpmetrics.yml` file in the root directory of your project. PhpMetrics will look for it and use it.

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
