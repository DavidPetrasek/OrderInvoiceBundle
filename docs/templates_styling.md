[Back to index](../README.md)

Templates & styling 
===================

## Templates

### mPDF and Twig
- `symfony console make:oib:invoice:mpdf_twig_template`
- Available styles: none (UPCOMING FEATURE)

#### Twig filters:
- `invoices_advance_totals` - Adds up totals of all advance invoices

## Styler
- Is available only in the `dev` environment
- Requires you to implement the [binary provider](./pdf_generation.md)

### Usage
Run: `symfony console oib:styler:enable`

Then visit: /_oib/styler/`orderID`/`invoiceType`
- `orderID` - If the chosen order or the specified invoice doesn't exist, dummy order and invoice is used
- `invoiceType` - allowed values: regular, advance, proforma, final