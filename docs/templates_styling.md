[Back to index](../README.md)

Templates & styling 
===================

## Templates

### mPDF and Twig
- `symfony console make:oib:invoice:mpdf_twig_template`
- Available styles: none (coming soon)


## Styler
- Is meant to be used in the dev environment
- Requires you to implement the [binary provider](./pdf_generation.md)

### Usage
Run: `symfony console oib:styler:enable`

Then visit: /_oib/styler/`orderID`/`invoiceType`
- `orderID` - If the chosen order or the specified invoice doesn't exist, dummy order and invoice is used
- `invoiceType` - allowed values: proforma, final