# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [2.8.2] - 2023-02-??

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
