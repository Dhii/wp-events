# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [next-version] YYYY-DD-MM
### Added
- `EventManager` can now optionally stop event propagation across all hook handlers.
- Added unit and functional tests.

### Changed
- Refactored codebase, separating logic into traits.
- Improved the event cache clearing mechanism for increased performance.

## [0.2] 2017-01-08
### Fixed
- Reflection parameter detection in `EventManager`.

## [0.1] 2016-07-07
Initial release.
### Added
- `EventManager` and `Event` classes.
