# Contribute

In order to run unit tests, please install the dev dependencies:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

Then, in order to run the test suite:

    ./vendor/bin/phpunit

Thanks for your help.

## Why the code is so old?

### Philosophy

PhpMetrics has several goals:
+ be stable
+ be performant
+ run on the **maximum of PHP versions** (PHP 5.3 to PHP 8.4)
+ be installable everywhere, **without dependency conflicts**

For these reasons, the code of PhpMetrics is intentionally written in "legacy" PHP.

### Dependencies

Not all projects are on the latest version of PHP, Symfony, or Laravel. Yes, there are projects that use Symfony 2. And these projects may also need metrics and quality.

For these reasons, PhpMetrics comes with the minimum of dependencies. Only the dependency on `nikic/php-parser` is accepted.

No Pull Request that modifies the `require` block will be accepted.


## Releasing

Please NEVER tag manually.

### Requirements

You will need to install :

+ [`docker`](https://www.docker.com/)
+ [make](https://www.gnu.org/software/make/)

### Usage

These commands will create `phar`, `debian` and `binary` release, 
then run all tests and push new release to Github:

```bash
make release VERSION=<VERSION>
# <VERSION> can be `major`, `minor` or `patch`
```
