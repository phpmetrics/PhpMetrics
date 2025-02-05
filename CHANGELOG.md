# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [3.0.0-rc8] - 2025-02-05

### Fixes
- Warning message on ExternalsVisitor.php
- PHP Infection rolled back to 0.28 as 0.29 is not compatible with PHP 8.1

### Updates
- Upgrade Psalm to 6.3.0

## [3.0.0-rc7] - 2025-02-04

### Fixes
- Fix numeric version array key in Composer\Packagist.php (thanks @Klemo1997)

### Updates
- Add OpenMetrics format (thanks @alanpoulain)
- Re-add Psalm as it is now compatible with nikic/php-parser v5
- Upgrade QA

## [3.0.0-rc6] - 2024-02-08

### Updates
- Upgrade to nikic/php-parser v5. Related BC Breaks are solved.
- Upgrade to PHPUnit 10.5
- Temporary remove Psalm as not compatible with nikic/php-parser v5.
- Remove Qodana as licence is mandatory

## [3.0.0-rc5] - 2024-01-17

### Fixed
- PHP8 Issue Uncaught TypeError: round()
- __Internal__: Unit tests that were not updated from latest changes
- __Internal__: docker-releasing process when built-in docker image is no longer available.

## [3.0.0-rc4] - 2023-09-15

### New features
- Add cyclomatic complexity for each method in a class
- Complexity table slightly updated to more comfortable table width

### Updates
- Upgrade to PHPUnit 10.3
- Upgrade to Psalm 5.15
- Upgrade to Qodana 2023.2-eap

## [3.0.0-rc3] - 2023-04-17

### Fixed
- Fatal error when analyzing snippets like `$a->{$b}` or `$a->{<some_expression>}`.
- Fatal error when analyzing snippets like `$a->{$b}()` or `$a->{<some_expression>}()`.

### Updates
- Update to PHPUnit 10.1

## [3.0.0-rc2] - 2023-04-17

### New features
- **Documentation**: Fix #491 by adding the possibility of running PhpMetrics via `sh` rather than `php`, for some OS.

### Fixed
- Fatal error when analyzing snippets like `$a->$b()` or `$c()`.
- Special versions are now taken into account on `artefacts/bintray.json` when a new release comes out.

## [3.0.0-rc1] - 2023-04-11

This new major version is containing lots of internal re-architecture processes in the source code, and upgrades the 
internal quality of PhpMetrics to a new level. Nevertheless, it contains also some BC Breaks and some metrics are 
remove. Please take a look at the detailed changelog below if you experiment any trouble.  

### BC Break
- PhpMetrics 3.0 is requiring PHP 8.1 minimum. To parse projects that are not yet in PHP 8.1, please use a docker 
  installation based on a PHP 8.1 Docker Image
- Git and JUnit plugins are no longer allowed in the configuration file

### Removed
- Installation and usage from Debian package. Please use the phar, composer, or docker instead
- **Metric removed**: Git related metrics
- **Metric removed**: PHPUnit related metrics
- **Metric removed**: PageRank metric
- **Metric removed**: On System Complexity, totalStructuralComplexity, totalDataComplexity and totalSystemComplexity. 
- **Metric removed**: On class-method enumeration, nbGetters and nbSetters are removed as no particular metric needs them. 

### New features
- __Internal__: Install a real dedicated QA system to secure PhpMetrics as mch as possible
- __Internal__: QA System is composed of PHP_CodeSniffer, PHPUnit, PHP Infection, PHPStan and Psalm and Qodana
- __Internal__: Make possible to create releases with suffixed names 
- Minor UX/UI improvements on the HTML report. 

### Fixed
- __Internal__: QA completely upgraded. PHPCS, PHPStan, Psalm and Qodana are no longer having errors except on baseline.
  Coverage is very close to 100%. All unit tests are passing. Mutation Score >94%. Some improvements are yet to come
- **Metric calculations**: Fix LCoM calculation that was not able to understand promoted properties in constructor.
- **Metric calculations**: Ignore PHP Attributes in the detection of getters and setters. This fixes LCoM calculation.
- **Metric calculations**: Improve calculation on Afferent Coupling and Efferent Coupling (+ related metrics) thanks to
  enlarged context of external classes usages detection.
- **Metric calculations**: Take NullSafeMethodCall (`$x?->y()`) into account for WeightMethodCount and Cyclomatic Complexity calculation.
- **Metric calculations**: Take NullSafePropertyFetch (`$x?->y`) into account for WeightMethodCount and Cyclomatic Complexity calculation.
- **Metric calculations**: Take newly introduced `match` structure into account for WeightMethodCount and Cyclomatic Complexity calculation.
- **Metric calculations**: Improve KanDefect metrics as `match` are now took into account as number of selects, along with switches.
- **Metric calculations**: On System Complexity, relative complexities are now calculated including NullSafeMethodCall (`$x?->y()`).

## [2.8.2] - 2023-03-08

### Fixed
- Fixed errors in HTML template. (thanks @Hikingyo and @gemal)
- Improved README.md contents. (thanks @kudashevs)
- Fixed junit parameter in JSON configuration file
- Minor removals of unnecessary source code
- Remove wrong artefacts causing download issues
- Add favicon to HTML rendered pages (thanks @gemal)
- Add version to CSS and script to counter cache (thanks @gemal)

## [2.8.1] - 2022-03-24

### Fixed
- Fixed issue with relative pat when using YAML configuration.

## [2.8.0] - 2022-03-23

### New features
- Allow to search for patterns of code.
- Possibility to add custom violation rules via configuration.
- Allow to use YAML for configuration
- Add `--metrics` option to display documentation about some metrics calculated and used by PhpMetrics.
- Exclude getters and setters from the CCN (cyclomatic complexity number) and LCoM (lack in cohesion of method) calculations
- Add `composer` option to enable or disable the composer packages analysis
- Add `--report-summary-json` option to report a summarized information from the calculated metrics.

### Fixed
- Fixed issue with some columns in HTML reports

## [2.7.4] - 2020-06-30

### Fixed
- Fixed compatibility issue where PHP 5 was no longer available on Debian systems  (#434)
- Fixed issue with display of charts in groups (#429, #433)

## [2.7.3] - 2020-06-27

### Fixed
- Fixed missing `composer.json` files when located in the root directory.

## [2.7.2] - 2020-06-27

### Fixed
- Fixed path of violations HTML templates.

## [2.7.1] - 2020-06-27

### Fixed
- Fixed error due to permission on generation of HTML report (#429)
- Fixed analysis on composer packages wrongly reported outdated when latest version is used. (#431)

## [2.7.0] - 2020-06-26

### New features
- Way to group analysis by layer

### Fixed
- Improved UI

## [2.6.2] - 2020-04-02

### Fixed
- Improved UI

## [2.6.1] - 2020-04-02

### Fixed
- Fixed undefined constant PROJECT_DIR (#426)

## [2.6.0] - 2020-03-28

### New features
- Way to download report
- Way to download chart
- Resolve PHP7 getters / setters (#405)
- Add metrics description file
- Add a carousel in the main HTML report page to display both graph at the same time

### Fixed
- Explicitly define the class \Hal\Component\Ast\NodeTraverser to make PhpMetrics work using composer --classmap-authoritative. (#402)
- Ensure the packagist license is an array, so they can be displayed. (#404)
- Fix warning "Division by zero" when no package is defined. (#401)

### Misc
- Move templates out of src
- Remove folders from phpcs

## [2.5.0] - 2019-12-11

### Changed
- Test the codebase against PHP 7.3 and 7.4

### Fixed
- Skip `self` and `parent` from external dependencies of dependency graph (#370) thanks to (@lencse)
- Don't leave notice when array is small in percentile function of loc report (#372) thanks to (@lencse)

## [2.4.1] - 2017-07-10

### Fixed
 - Fix parsing errors with PHP < 7 (#360, #361)
 - Remain CCN for backward compatibility (#359, #362)
 
### Deprecated
 - CCN by classes is deprecated and will be removed in the next major release (#359, #362)

## [2.4.0] - 2017-07-09

### Added
 - Added package metrics (#283)

### Changed
 - Enhanced composer package comparison (#337, #342, thanks @juliendufresne)
 - Better PHP 7 support (#335, #334, #336 thanks @carusogabriel)
 - Support nikic/php-parser:^4 (#345, #347)

### Fixed
 - Refine Cyclomatic Complexity Metric (#343, #344, #353, #357, #358, thanks @fabianbadoi)
 - Improved composer package version comparison (#337, thanks @juliendufresne)
 - Resolved root path exclusion conflict (#355, thanks @fabianbadoi)
 - Fixed getter and setter detection with types (#335, #336, thanks @jakagacic)
 - Fixed documentation URL (#321, thanks @ottaviano)
 - Fix non unique block ids in HTML output (#356, thanks @dumith-erange)
 - Fix rounding of metrics (#339, thanks @ssfinney)
