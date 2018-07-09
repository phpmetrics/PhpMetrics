# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.4.0] - 2017-07-09

### Added
 - Added package metrics (#283)

### Changed
 - Enhanced composer package comparison (#337, #342 thanks @juliendufresne)
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
