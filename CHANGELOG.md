# Changelog
All notable changes to this project are documented in this file.


## [1.2.2] - 2025-07-18
### Added
Commands and makers for easy configuration and usage:
- `symfony console oib:configure`
- `symfony console make:oib:category`
- `symfony console make:oib:cron_controller`

### Changed

### Fixed


## [1.2.0] - 2025-07-07
### Added

### Changed
- `MpdfExporter` was replaced by improved `MpdfGenerator`
- `config/packages/psys_order_invoice.yaml` is no longer required and should be removed

### Fixed


## [1.1.0] - 2025-06-30
### Added
- `currency` field added to the `Order` entity, storing ISO 4217 alpha‑currency codes (e.g. "USD", "EUR")

### Changed

### Fixed
- Custom categories now have to implement the CategoryInterface