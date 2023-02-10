# Contribute

In order to let everyone contribute to this project, we aim to have the minimum software dependencies.

Each developer is using a different workspace, with different software programs and different versions of these.  
Contributing to PhpMetrics should not force you to install another program, or change your local configuration.

Therefore, since the version 3 of PhpMetrics, we are using [Docker](https://docs.docker.com/get-docker/) to create 
containers in which each step can be executed in an isolated system for your workspace.

You only need to have Docker installed and configured on your local machine.

## First step: building the "tools" Docker image

All the tools you need to use during your contribution are stored in the same Docker image to make it easy to use.
This image is called "phpmetrics_tools" and needs first to be set up. To do this, you only have to run a simple make 
command:
```shell
$> make build-qa-tools
```
By running this, you should see `Installing PhpMetrics tools…` message in your output. Once done, you have access to
everything you need to contribute to PhpMetrics. Then, there will be no need to run this target anymore: the Docker 
image is created and available.

> :information_source: If you are changing the content of this Docker image and need to rebuild it (as 
> `make build-qa-tools` does nothing if the docker image is already created), you need to run another target
> to update the image:
> ```shell
> $> make rebuild-qa-tools
> ```

## Composer

To install the Composer dependencies of PhpMetrics, we have a make target predefined to help you:
```shell
$> make vendor
```
This make target combines the verification of the `composer.json` syntax, the actual need to run `composer install` 
based on your current `composer.lock` status, and all of these even if you do not have Composer installed on your local 
machine, thanks to Docker and our "phpmetrics_tools" Docker image. 

### Update the dependencies

If you need to update your dependencies, use the following make target:
```shell
$> make vendor-update
```

## Checking sources before start: Quality Assurance

Since the version 3 of PhpMetrics, lots of Quality Assurance (QA) tools have been added so any contributor can very 
easily check the quality of their proposal. But even before checking the proposition, as a contributor, you may want to
check everything is OK **before** to start.

A simple command to run all tools is available from the project root directory:
```shell
$> make qa
```

This command will run all QA tools available, listed as following:

### PHP Infection

> :information_source: To only run this tool:
> ```shell
> $> make infection
> ```
> :warning: PHP Infection needs results from PHPUnit and PHP CodeCoverage. Therefore, when running this tool, you will 
> automatically run PHPUnit on first place.

The [PHP Infection](https://github.com/infection/infection) tool is creating mutations of the source code (unwanted 
changes) and ensures the associated unit tests are no longer passing after this change. If it still passes, then the 
related unit test is not strong enough and must be reinforced.

More information on the [documentation of PHP Infection](https://infection.github.io/).

### PHPUnit

> :information_source: To only run this tool:
> ```shell
> $> make phpunit
> ```

This will execute the available unit tests of the project, and generates a CodeCoverage report that will be used by PHP 
Infection.

### PHP Code Sniffer

> :information_source: To only run this tool:
> ```shell
> $> make phpcs
> ```

This tool ensures all source code is following a style ruleset defined in PhpMetrics.

More information on the [repository of PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer).

> :information_source: Some errors related to PHPCS can be automatically fixed with the following command:
> ```shell
> $> make phpcbf
> ```

### Psalm

> :information_source: To only run this tool:
> ```shell
> $> make psalm
> ```

[Psalm](https://github.com/vimeo/psalm) is a static analyzer, which means it does not need to execute the code to detect
issues.

More information on the [documentation of Psalm](https://psalm.dev/docs/).

### PHPStan

> :information_source: To only run this tool:
> ```shell
> $> make phpstan
> ```

[PHPStan](https://github.com/phpstan/phpstan) is also a static analyzer like Psalm.

More information on the [documentation of PHPStan](https://phpstan.org/user-guide/getting-started).

### Qodana

> :information_source: To only run this tool:
> ```shell
> $> make qodana
> ```

[Qodana](https://www.jetbrains.com/qodana/) is analyzing the project over tons of inspections and rules provided by 
JetBrains. If you are using PhpStorm as IDE, you can include the profile in your settings to directly benefit of the
inspections reports on runtime during your development.

## Releasing

You will need to install :

+ [`docker`](https://www.docker.com/)
+ [make](https://www.gnu.org/software/make/)

### Usage

These commands will create `phar`, `debian` and `binary` release,
then run all tests and push new release to GitHub:

```bash
make release VERSION=<VERSION>
# <VERSION> can be `major`, `minor` or `patch`
```
