# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [0.3.0] - 2021-06-22
Stable release.

## [0.3.0-alpha2] - 2018-05-29
### Fixed
- Events no longer receive only the first hook argument.

## [0.3.0-alpha1] - 2018-05-11
### Added
- `EventManager` can now optionally stop event propagation across all hook handlers.
- Added unit and functional tests.

### Changed
- Refactored codebase, separating logic into traits.
- Improved the event cache clearing mechanism for increased performance.

## [0.2.0] - 2017-01-08
### Fixed
- Reflection parameter detection in `EventManager`.

## [0.1.0] - 2016-07-07
Initial release.
### Added
- `EventManager` and `Event` classes.
