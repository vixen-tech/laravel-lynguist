# Changelog

## 1.2.0 [2026-07-27]

### Added

- Added `POST /lynguist/sync` webhook to sync translations from Lynguist.com.

### Changed

- Empty translation values now use `null` instead of `""` (empty string) when scanning, merging, and downloading translations.
- Renamed npm package from `@vixen/lynguist` to `@vixen-tech/lynguist`.

## 1.1.0 [2026-04-16]

### Added

- Added support for Laravel 13.

## 1.0.3 [2026-02-08]

### Added

- Added progress bars and spinners to commands.

## 1.0.2 [2026-02-08]

### Added

- Added `php artisan lynguist:download` command.

## 1.0.1 [2026-02-07]

### Added

- Added license.

## 1.0.0 [2026-02-07]

First release.
