[Back to index](../README.md)

PDF generation
==============

### Generator
Generator returns the PDF as binary data. If you need to save the invoice to disk, please see [File management](./file_management.md)

Available generators: `MpdfGenerator`

Example:
``` php
use Psys\OrderInvoiceBundle\Service\InvoiceGenerator\MpdfGenerator;

$mpdfGenerator->generate($htmlPDF, 
    // Optional mPDF config
    [
        'margin_left' => 0,
        'margin_right' => 0,
    ],
    // Optional callback to add background graphics 
    function (\Mpdf\Mpdf $mpdf) 
    {
        $mpdf->SetFillColor(0, 200, 255);
        $mpdf->RoundedRect(20, 30, 30, 30, 0, 'F');
    },
    // Optional callback to add overlay graphics 
    function (\Mpdf\Mpdf $mpdf) 
    {
        $mpdf->SetFillColor(0, 255, 50);
        $mpdf->RoundedRect(35, 45, 30, 30, 0, 'F');
    }
);
```

### Custom binary provider (optional)
- Avoid code duplication and be more organized
- Works with the [Styler](./templates_styling.md)

Example implementation:
``` php
// ./src/Service/OrderInvoiceBundle.php
<?php
namespace App\Service\OrderInvoiceBundle;

use Psys\OrderInvoiceBundle\Service\InvoiceBinaryProvider\AbstractInvoiceBinaryProvider;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Psys\OrderInvoiceBundle\Service\InvoiceGenerator\MpdfGenerator;
use Twig\Environment;


class InvoiceBinaryProvider extends AbstractInvoiceBinaryProvider
{
    public function __construct
    (
        private readonly Environment $twig,
        private readonly MpdfGenerator $mpdfGenerator
    ) 
    {}

    public function getBinary(Order $ent_Order, InvoiceType $invoiceType): string
    {
        $htmlPDF = $this->twig->render('invoice/oi_default.html.twig', 
            [
                'ent_Order'  => $ent_Order,
                'invoiceType'  => $invoiceType->name,
            ]);
        return $this->mpdfGenerator->generate($htmlPDF);
    }

    public function getProforma(Order $ent_Order): string
    {
        return $this->getBinary($ent_Order, InvoiceType::PROFORMA);
    }

    public function getFinal(Order $ent_Order): string
    {
        return $this->getBinary($ent_Order, InvoiceType::FINAL);
    }
}
```