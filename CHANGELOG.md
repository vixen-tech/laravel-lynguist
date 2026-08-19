# Changelog

## 1.2.3 [2026-08-19]

### Fixed

- `Lynguist::sync()`, `Lynguist::store()`, and `Lynguist::generateTypeScriptFile()` now create the output directory (recursively, if needed) when it doesn't already exist, instead of failing to write.

## 1.2.2 [2026-08-09]

### Added

- Added a `$merge` parameter to `Lynguist::sync()`. When `true`, incoming translations are merged into the existing language file instead of replacing it entirely, overwriting matching keys while preserving keys not present in the incoming translations.

### Changed

- `php artisan lynguist:download` now syncs with `merge: true`, so downloading translations no longer discards existing translations that aren't part of the response.
- `php artisan lynguist:download` now regenerates the TypeScript declaration file after syncing, keeping it in sync with downloaded translations.

## 1.2.1 [2026-07-28]

### Added

- The service provider now automatically shares translations as an Inertia `lynguist` prop on every response when Inertia is installed, enabling runtime translation updates without a frontend rebuild.

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
