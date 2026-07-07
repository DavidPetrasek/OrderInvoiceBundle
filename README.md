[![Packagist Downloads](https://img.shields.io/packagist/dm/psys/order-invoice-bundle?style=flat)](https://packagist.org/packages/psys/order-invoice-bundle)


# OrderInvoiceBundle

**Perfect for SaaS platforms, digital product sales, online courses, and service providers** who need a robust invoicing system without the bloat of a full e-commerce engine. It supports complex, multi-step accounting flows out of the box.

### Features

- **Complete Order & Invoice Lifecycle:** Seamlessly manage service orders and their associated financial documents.
- **Invoice Types:** Native support for the full accounting cycle:
  - `Proforma`
  - `Advance` (Deposit)
  - `Regular` (Standard)
  - `Final` (Closing invoice linked with advance payments)
- **PDF Generation:** Generate custom professional PDF invoices for your customers.
- **File Management:** Build-in logic to persist, retrieve, and delete generated invoice files securely.


## Installation

Minimal requirements:
- Symfony 7.2
- PHP 8.2

Run: 
``` bash
composer req psys/order-invoice-bundle
```

Finish installation: 
``` bash
symfony console oib:install
```

[Upgrading guide](./UPGRADING.md)

[Documentation](./docs/index.md)