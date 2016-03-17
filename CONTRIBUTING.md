# Contribution Guidelines

Thank you for your interest in contributing to WP404!

This document is meant to outline the general mission, development approach, and driving principles behind the WP404 project.


## Mission

WP404 is meant to be a framework to enable developers and site owners to analyze 404 errors on their sites. By gaining better insight into *why* a user was given a 404 error, we hope to provide a better user experience with fewer users lost or frustrated due to broken links.


## Development

To get started, clone [the WP404 GitHub repository](https://github.com/stevegrunwell/wp404) into your local development environment. The repository should be installed the same way you would a plugin (as a subdirectory of wp-content/plugins), and should be activated through the WordPress admin dashboard.

Once your copy of WP404 is installed, run `composer install` to install the development dependencies, including [WP Enforcer](https://github.com/stevegrunwell/wp-enforcer), [PHPUnit](https://phpunit.de/), and [WP_Mock](https://github.com/10up/wp_mock).


### Coding standards

This project adheres to [the WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/), which are enforced by way of [WP Enforcer on a pre-commit Git hook](https://github.com/stevegrunwell/wp-enforcer). Beyond the functional code, [the project also follows the WordPress Inline Documentation Standards](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/).


### Branching

All work should be done in feature branches, branched off of the `develop` branch (the default branch of the repository). Once your feature is complete, please [open a pull request](https://help.github.com/articles/creating-a-pull-request) against the primary repository's `develop` branch.


### Unit testing

[![Test Coverage](https://codeclimate.com/github/stevegrunwell/wp404/badges/coverage.svg)](https://codeclimate.com/github/stevegrunwell/wp404/coverage)

WP404 attempts to provide as much test coverage as reasonable through PHPUnit and WP_Mock. When submitting new code, please continue this tradition by providing appropriate test coverage for your changes. [Travis-CI](https://travis-ci.org/stevegrunwell/wp404) is also configured to automatically run all new pushes, and pull requests that break the build will not be accepted without remediation.
