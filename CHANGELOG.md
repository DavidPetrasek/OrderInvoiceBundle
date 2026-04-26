# Changelog
All notable changes to this project are documented in this file.


## [1.4.6] - 2026-04-26
### Added
- Missing types for storage path: advance, regular
- Autowiring support: `InvoiceAdvanceRepository`, `InvoiceFinalRepository`, `InvoiceProformaRepository`, `InvoiceRegularRepository`, `InvoiceRepository`

### Changed
- Due date is not mandatory anymore

### Fixed



## [1.4.2] - 2026-03-17
### Added
- Twig filter `invoices_advance_totals`
- Proforma invoice: payable

### Changed
- docs

### Fixed
- Twig template


## [1.4.0] - 2026-03-15
### Added
- Invoice types: Regular, Advance
- Order: `State::PARTIALLY_PAID`
- Final invoice: due date
- docs: Invoice types, Creating a new invoice
- Invoice restrictions upon creation/edition
- tests

### Changed
- Order: `State::NEW` to `State::UNPAID`
- OrderManager: `processAndSaveNewOrder` to `save`
- Entity: `OrderItem` to `Item`
- Invoice: `variable_symbol` to `payment_reference`

### Fixed
- Install command


## [1.3.3] - 2026-03-11
### Added

### Changed

### Fixed
Prevent deletion of parent entity.
- Run: `symfony console make:migration` --> `symfony console doctrine:migrations:migrate`


## [1.3.1] - 2025-12-14

### Added
- Invoice styler

### Changed

### Fixed
- Docs
- Config formatting


## [1.3.0] - 2025-12-08

### Added
- Default File entity (new table: oi_file)
- File persister/deleler: Persisting/deleting invoice to/from disk is super easy now
- mPDF generator: Add custom backgroud/overlay graphics
- UPGRADING.md
- docs

### Changed
- `symfony console oib:configure` to `symfony console oib:install`
- README.md

### Fixed



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