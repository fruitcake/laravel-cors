# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## v0.9.0 (2016-03-2017)
### Breaking changes
 - The Lumen ServiceProvider has been removed. Both Laravel and Lumen should use `Barryvdh\Cors\ServiceProvider::class`.
 
### Added
 - Better error-handling when exceptions occur.
 - A lot of tests, also on older Laravel versions.
