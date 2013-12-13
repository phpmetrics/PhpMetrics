# PhpMetrics

Gives metrics about PHP project and classes.

# Installation

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

# Usage

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

