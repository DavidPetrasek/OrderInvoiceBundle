[Back to index](index.md)

PDF generation
==============

### Generator
Generator returns the PDF as binary data. If you need to save the invoice to disk, please see [File management](./file_management.md)

Available generators: `MpdfGenerator`

Example:
``` php
use Psys\OrderInvoiceBundle\Service\InvoiceGenerator\MpdfGenerator;

$mpdfGenerator
    ->useCss('/abs_path/to/style.css') // Optional
    ->generate($htmlPDF, 
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

``` yaml
# config/packages/psys_order_invoice.yaml
psys_order_invoice:
    invoice_binary_provider: App\Service\OrderInvoiceBundle\InvoiceBinaryProvider
```

``` php
<?php
namespace App\Service\OrderInvoiceBundle;

use Psys\OrderInvoiceBundle\Service\InvoiceBinaryProvider\AbstractInvoiceBinaryProvider;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Psys\OrderInvoiceBundle\Service\InvoiceGenerator\MpdfGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;


class InvoiceBinaryProvider extends AbstractInvoiceBinaryProvider
{
    public function __construct
    (
        private readonly Environment $twig,
        private readonly MpdfGenerator $mpdfGenerator,
        #[Autowire('%kernel.project_dir%')] private string $projectDir
    ) 
    {}

    public function getBinary(Order $order, InvoiceType $invoiceType, ?InvoiceAdvance $invoiceAdvance = null): string
    {
        $htmlPDF = $this->twig->render('invoice/oi_default.html.twig', 
            [
                'order'  => $order,
                'invoiceType'  => $invoiceType->name,
                'invoiceAdvance'  => $invoiceAdvance,
            ]);
        return $this->mpdfGenerator
            ->useCss($this->projectDir.'/assets/css/invoice/default_mpdf.css')
            ->generate($htmlPDF);
    }

    public function getRegular(Order $order): string
    {
        return $this->getBinary($order, InvoiceType::REGULAR);
    }

    public function getProforma(Order $order): string
    {
        return $this->getBinary($order, InvoiceType::PROFORMA);
    }

    public function getAdvance(InvoiceAdvance $invoiceAdvance): string
    {
        return $this->getBinary($invoiceAdvance->getOrder(), InvoiceType::ADVANCE, $invoiceAdvance);
    }

    public function getFinal(Order $order): string
    {
        return $this->getBinary($order, InvoiceType::FINAL);
    }
}
```