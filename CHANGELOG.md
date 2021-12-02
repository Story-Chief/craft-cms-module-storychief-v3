# Storychief v3 Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.0.10 - 2021-12-02
### Fixed
- Fixed WebhookController sites query for installations with table prefix (e.g. `craft_`).

## 1.0.9 - 2021-08-26
### Added
- Added beforeEntryPublish and beforeEntryUpdate events which allow altering of the entry before it is saved.

## 1.0.6 - 2020-08-17
### Added
- Support for propagation settings: none, language, siteGroup and custom. Previously only all

## 1.0.5 - 2020-08-11
### Fixed
- Improved error logging

## 1.0.4 - 2019-11-12
### Fixed
- Setting custom field value directly on the element is no longer possible

## 1.0.3 - 2019-11-12
### Fixed
- Translations overwrite source article

## 1.0.2 - 2019-09-18
### Fixed
- Redirect on settings save
- Entry field type

## 1.0.1 - 2019-07-17
### Added
- Publish events

## 1.0.0 - 2019-04-16
### Added
- Initial release
