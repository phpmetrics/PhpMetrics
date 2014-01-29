# PhpMetrics

Gives metrics about PHP project and classes.

[![Build Status](https://secure.travis-ci.org/Halleck45/PhpMetrics.png)](http://travis-ci.org/Halleck45/PhpMetrics)  [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Halleck45/PhpMetrics/badges/quality-score.png?s=b825f35680c0a469333da2c963226828fed135ba)](https://scrutinizer-ci.com/g/Halleck45/PhpMetrics/)

# Installation

## As Phar archive

    wget https://github.com/Halleck45/PhpMetrics/raw/master/build/metrics.phar
    php metrics.phar <folder or filename>
    
## With sources:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
    php ./bin/metrics.php <folder or filename>

Will output:


```
file1.php:
	Halstead:
		Volume: 327.43
		Length: 86
		Vocabulary: 14
		Effort: 163
		Difficulty: 0.5
		Delivred Bugs: 0.04
		Time: 9.1
	LOC:
		LOC: 68
		Logical LOC: 42
		Cyclomatic complexity: 2
	Maintenability:
		Maintenability Index: 83.78

file2.php:
    ...
```

## Bubbles chart and complete report

If you want to get the complete report (html):

    php ./bin/metrics.php --format=html <folder or filename> > /path/of/your/choice.html

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

    Maintainability Index = 171 - 5.2 * ln(Halstead Volume) - 0.23 * (Cyclomatic Complexity) - 16.2 * ln(Lines of Code))*100 / 171

Generally:

+ 0-9 = Danger
+ 10-19 = Warning
+ 20-100 = Ok



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
