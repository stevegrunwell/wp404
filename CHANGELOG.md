# Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

### Added

* A new reporter, `wp_query`, that tracks the contents of the `$wp_query` variable.

### Changed

* The `post_exists` reporter will now return a string ("No matching post data was found.") rather than an empty array if we're unable to parse any post data.

[Unreleased]: https://github.com/stevegrunwell/wp404/compare/v0.1.0...master