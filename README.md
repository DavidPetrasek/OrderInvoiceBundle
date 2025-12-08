[![Packagist Downloads](https://img.shields.io/packagist/dm/psys/order-invoice-bundle?style=flat)](https://packagist.org/packages/psys/order-invoice-bundle)


# OrderInvoiceBundle

### Use case
- You're not running a typical online store — full-featured e-commerce platform would be overkill.

### Features
- manage orders and associated invoices
- generate invoices in PDF format
- file management: persist/delete invoices


## Installation

Minimal requirements:
- Symfony 7
- PHP 8.1

Run: `composer req psys/order-invoice-bundle`

Finish installation: `symfony console oib:install`


## Usage

[Creating a new order](./docs/new_order.md)

[PDF generation](./docs/pdf_generation.md)

[File management](./docs/file_management.md)

[Other](./docs/other.md)