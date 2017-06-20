# PhpMetrics Change Log

## [Unreleased]
### Fixed
- Wrong PMD priority levels (@krukru) [#288](https://github.com/phpmetrics/PhpMetrics/issues/288)

## [2.2.0] - 2017-04-13
### Added
- New metric: `ccnMethodMax` (maximum cyclomatic complexity of methods for class)
- New report: composer dependencies versions
- New report: composer dependencies licenses
- HTML report is now responsive
### Changed
- Simplified README
### Fixed
- --report-violations Class not found [#276](https://github.com/phpmetrics/PhpMetrics/issues/276)

## [2.1.0] - 2017-04-10
### Added
- Improved Junit report
- Improved UI
- Improved CI
### Removed
- Removed support of PHP 5.4
- Removed Symfony/Console
### Fixed
- Fixed majors bugs with code parsing

## 2.0.0 - 2017-02-01
### Changed
- php7 is now the main version for building artifacts

[Unreleased]: https://github.com/phpmetrics/PhpMetrics/compare/v2.2.0...HEAD
[2.2.0]: https://github.com/phpmetrics/PhpMetrics/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/phpmetrics/PhpMetrics/compare/v2.0.0...v2.1.0
