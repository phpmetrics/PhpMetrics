# PhpMetrics

Gives metrics about PHP project and classes.

[![Build Status](https://secure.travis-ci.org/Halleck45/PhpMetrics.png)](http://travis-ci.org/Halleck45/PhpMetrics)  [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Halleck45/PhpMetrics/badges/quality-score.png?s=b825f35680c0a469333da2c963226828fed135ba)](https://scrutinizer-ci.com/g/Halleck45/PhpMetrics/)

# Installation

## As Phar archive

    wget https://github.com/Halleck45/PhpMetrics/raw/master/build/metrics.phar
    php metrics.phar <folder or filename>

Will output:

![Standard report](http://halleck45.github.io/PhpMetrics/images/report-standard.png)

## Bubbles chart and complete report

If you want to get the summary HTML report (with charts):

    php ./bin/metrics.php --summary-html=/path/of/your/choice.html <folder or filename>

You can change the depth of the summary report with the `--level=<value>` option.

If you want to have a detailled view (file by file):

    php ./bin/metrics.php --details-html=/path/of/your/choice.html <folder or filename>

## Informations about OOP model

If you want to get informations about OOP model (coupling, instability...), you should pass the `--oop` parameter:

    php ./bin/metrics.php --oop <folder or filename>

Remember that this feature parse all files, extract declared classes, dependencies of each method... and is really *very slow*.

## Jenkins and PIC integration

You can easily export resut to XML with the `--summary-xml` option:

    php ./bin/metrics.php --summary-xml=/path/of/your/choice.xml <folder or filename>

You will find a tutorial to [integrate PhpMetrics report to Jenkins here](blog.lepine.pro/industrialisation/indice-de-maintenabilite-dun-projet-php-et-jenkins) (in French).

### Read report

+ Each file is symbolized by a circle
+ Size of the circle represents the Cyclomatic complexity
+ Color of the circle represents te Maintenability Index
+ Move your cursor on a circle to have details

Large red circles will be probably hard to maintain.

### Example : Symfony2 Component

[open full report](http://halleck45.github.io/PhpMetrics/report/symfony2-component/index.html)

![Symfony2 report](http://halleck45.github.io/PhpMetrics/images/preview-symfony2-component.png)

### Example : Zend Framework 2

[open full report](http://halleck45.github.io/PhpMetrics/report/zendframework2/index.html)

![Symfony2 report](http://halleck45.github.io/PhpMetrics/images/preview-zendframework2.png)


# Metrics

## Halstead complexity

This indicator provides:

+ Program length (N)
+ Vocabulary size (n)
+ Program volume (V)
+ Difficulty level (D)
+ Effort to implement (E)
+ Time to implement, in seconds (T)
+ Number of delivered bugs (B)

```
N = N1 + N2
n = n1 + n2
V = N * log2(n)
D = ( n1 / 2 ) * ( N2 / n2 )
E = V * D
T = E / 18
B = ( E ** (2/3) ) / 3000
```

## Complexity index

According Wikipedia, Maintainability Index is a software metric which measures how maintainable (easy to support and change) the source code is.
The maintainability index is calculated as a factored formula consisting of Lines Of Code, Cyclomatic Complexity and Halstead volume.

    MIwoc: Maintainability Index without comments
    MIcw: Maintainability Index comment weight
    MI: Maintainability Index = MIwoc + MIcw
    MIwoc = 171 - 5.2 * ln(Halstead Volume) - 0.23 * (Cyclomatic Complexity) - 16.2 * ln(Lines of Code))*100 / 171
    MIcw = 50 * sin(sqrt(2.4 * perCM))
    MI = MIwoc + MIcw

## Maintainability Index Comment weight

Comment weight represents the impact of documentation in code.

    perCM = commentLoc / loc
    MIcw = 50 * sin(sqrt(2.4 * perCM))

## Coupling and instability

Coupling use two metrics:

+ Afferent coupling (CA): number of classes that your classes affects
+ Efferent coupling (CE) : number of classes used by your class

Instability concerns the risk of your class, according coupling:

    I = CE / (CA + CE)

# Use it in code

## Halstead

```php
$halstead = new \Halstead\Halstead(new \Token\TokenType());
$rHalstead = $halstead->calculate($filename);
var_dump($rHalstead);
```

## PHPLoc

This component uses [phploc](https://github.com/sebastianbergmann/phploc).

```php
$loc = new \Loc\Loc();
$rLoc = $loc->calculate($filename);
var_dump($rLoc);
```

## Maintenability Index

```php
$maintenability = new \MaintenabilityIndex\MaintenabilityIndex;
$rMaintenability = $maintenability->calculate($rHalstead, $rLoc);
var_dump($rMaintenability);
```

## OOP Extractor

Extracts OOP model of files, and map classes and files:

```php
$extractor = new Extractor();
$rOOP = $extractor->extract($filename);
var_dump($rOOP);
```

## Coupling

Calculate coupling.

```php
// build class map
$classMap = new ClassMap;
foreach($files as $filename) {
    $extractor = new Extractor();
    $rOOP = $extractor->extract($filename);
    $classMap->push($filename, $rOOP);
}
// coupling
$coupling = new Coupling;
$couplingMap = $coupling->calculate($classMap);
$rCoupling = $couplingMap->get('\My\Namespace\ClassName');
var_dump($rCoupling);
```

If you want to work with files instead of classes:

```php
// reuse code above, then
$fileCoupling = new FileCoupling($classMap, $couplingMap);
$rCoupling = $fileCoupling->calculate('/path/to/file.php');
var_dump($rCoupling);
```



# Contribute

In order to run unit tests, please install dev dependencies:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --dev

Then, to run the test suite:

    ./vendor/bin/phpunit -c phpunit.xml.dist

# Author

+ Jean-François Lépine <[blog.lepine.pro](http://blog.lepine.pro)>

# Licence

See the LICENCE file
